<?php

/**
 * location where your media files are located.
 */
define('MOVIE_ROOT', '/home/torrents/done');

/**
 * if NO_FIFO is set to true, then disregard any fifo stuff. this is
 * useful for testing the web app without having a functioning mplayer
 * daemon running.
 */
define('NO_FIFO',FALSE);
define('MPLAYER_FIFO', '/home/everyone/movies/mplayer');
define('XSET_FIFO', '/home/everyone/movies/xset');

/**
 * use sessions to remember the last path so we can keep browsing
 * smoothly between things.
 */
session_start();

?><!DOCTYPE html>
<html>
<head>
<title>play something</title>
<meta name="viewport" content="initial-scale=1.0,user-scalable=no,width=device-width"/>
<link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css" />
<script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
<script src="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.js"></script>
<style>
  .panel-content { }
  body, html { padding: 0; margin: 0 }
  .header-bar { padding: 5px }
</style>
</head>
<body>
<div data-role="page">
<div data-role="panel" id="controls" data-position="right"
data-display="reveal" data-dismissible="true">
<div class="panel-content">
<h4>Volume:</h4>
<div data-role="controlgroup" data-type="horizontal">
<a class="json" data-role="button" data-icon="plus" data-ajax="false"
   data-mini="true" data-iconpos="notext" href="json.php?cmd=volup">Volume Up</a>
<a class="json" data-role="button" data-icon="minus" data-ajax="false"
   data-mini="true" data-iconpos="notext" href="json.php?cmd=voldown">Volume Down</a>
</div>
<form method="get" action="json.php" id="wtform">
<div data-role="fieldcontain">
  <input type="range" name="vol" id="vol" value="<?php 
      echo empty($_SESSION['vol']) ? 50 : $_SESSION['vol'];
    ?>" min="0" max="100"  />
  <input type="hidden" name="cmd" value="vol" />
  <input type="submit" name="submit" data-mini="true" value="change" />
</div>
</form>
<h4>Position:</h4>
<div data-role="controlgroup" data-type="horizontal">
<a class="json" data-role="button" data-icon="arrow-l"
   data-ajax="false" data-mini="true" data-iconpos="notext"
   href="json.php?cmd=rrew">Really Rewind</a>
<a class="json" data-role="button" data-icon="back" data-ajax="false"
   data-mini="true" data-iconpos="notext" href="json.php?cmd=rew">Rewind</a>
<a class="json" data-role="button" data-icon="forward"
   data-ajax="false" data-mini="true" data-iconpos="notext"
   href="json.php?cmd=fwd">Forward</a>
<a class="json" data-role="button" data-icon="arrow-r"
   data-ajax="false" data-mini="true" data-iconpos="notext"
   href="json.php?cmd=ffwd">Fast Forward</a>
</div>
<h4>Display:</h4>
<div data-role="controlgroup" data-type="horizontal">
<a class="json" data-role="button" data-ajax="false" data-mini="true" href="json.php?cmd=osd">OSD</a>
<a class="json" data-role="button" data-ajax="false" data-mini="true" href="json.php?cmd=sub_select">Subs</a>
</div>
</div>
</div>
<div data-role="header" class="header-bar">
<div data-role="controlgroup" data-type="horizontal" style="float:left">
<a data-role="button" data-icon="home" data-ajax="false" href="?path=/home/torrents/done">home</a>
<a class="json" data-role="button" data-ajax="false" href="json.php?cmd=pause">Play/Pause</a>
<a class="json" data-role="button" data-ajax="false" href="json.php?cmd=stop">Stop</a>
</div>
<div align="right" data-role="controlgroup" data-type="horizontal">
<a data-role="button" data-icon="bars" data-iconpos="notext" href="#controls">Controls</a>
</div>
</div>
<div data-role="content">
<?php

$path = NULL;
if (!empty($_GET['path']))
  $path = $_GET['path'];

if (empty($path) && !empty($_SESSION['path']))
  $path = $_SESSION['path'];

if (is_file($path))
  $path = dirname($path);

/**
 * don't go below MOVIE_ROOT. this is useful if you dont' want to
 * descend below a particular direcotry, but be careful, it might be
 * weird sometimes, eg using symlinks.
 *
 * if (!is_dir($path) || join(DIRECTORY_SEPARATOR,
 * array_slice(explode(DIRECTORY_SEPARATOR, $path),0,
 * sizeof(explode(DIRECTORY_SEPARATOR,MOVIE_ROOT)))) != MOVIE_ROOT )
 *
 */
if (!is_dir($path)) $path = MOVIE_ROOT;

if ($h = opendir($path))
{
  #print("<h6>$path</h6>\n");
  print("<ul data-role=\"listview\" data-filter=\"true\">\n");
  while (false !== ($entry = readdir($h))) {
    $t_path = realpath("$path/$entry");
    $t_path_str = urlencode($t_path);
    $class = $href = "";
    if (is_file($t_path))
    {
      $class = "json file";
      $href = "json.php";
    }
    print("<li><a class=\"$class\" data-transition=\"slide\" href=\"$href?cmd=loadfile&path=$t_path\">$entry</a></li>\n");
  }
  closedir($h);
  print("</ul>\n");
}

$_SESSION['path'] = $path;

?>
</div>
</div>
<script>
// stuff here
function talk_cute(href)
{
  $.getJSON(href, function(data){
    if(!data.result)
    {
      $('#content').replaceWith('failed...');
    }
  });
}

$('a.json').on('click', function(e){
  e.preventDefault();
  talk_cute($(this).attr('href'));
});

$('#wtform').submit(function(e){
  talk_cute('json.php?' + $(this).serialize());
  return false;
});

$(document).on('swipeleft', function(e, ui){
  $('#controls').panel('open', {display:'push',position:'right'});
});

</script>
</body>
</html>
