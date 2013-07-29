<?php

/**
 * location where your media files are located.
 */
define('MOVIE_ROOT', '/home/everyone');

/**
 * if NO_FIFO is set to true, then disregard any fifo stuff. this is
 * useful for testing the web app without having a functioning mplayer
 * daemon running.
 */
define('NO_FIFO',FALSE);
define('MPLAYER_FIFO', '/home/everyone/mplayerfifo');
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
<meta content="text/html;charset=utf-8" http-equiv="Content-Type"/>
<meta content="utf-8" http-equiv="encoding"/>
<meta name="viewport" content="initial-scale=1.0,user-scalable=no,width=device-width"/>
<link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css" />
<script src="jquery-1.9.1.min.js"></script>
<script src="jquery.mobile-1.3.2.min.js"></script>
<style>
  .panel-content { text-align: center; }
  body, html { padding: 0; margin: 0 }
  .header-bar { padding: 5px }
</style>
</head>
<body>
<div data-theme="a" data-role="page">
<div data-theme="a" data-role="panel" id="controls" data-position="right"
data-display="overlay" data-dismissible="true">
<div class="panel-content">
<h4>Volume</h4>
<div data-role="controlgroup" data-type="horizontal">
<a class="json" data-role="button" data-icon="minus" data-ajax="false" data-mini="true" data-iconpos="notext" href="json.php?cmd=voldown">Volume Down</a>
<a class="json" data-role="button" data-icon="plus" data-ajax="false" data-mini="true" data-iconpos="notext" href="json.php?cmd=volup">Volume Up</a>
</div>
<form method="get" action="json.php" id="wtform">
<div data-role="fieldcontain">
  <input type="range" name="vol" id="vol" value="<?php echo empty($_SESSION['vol']) ? 50 : $_SESSION['vol']; ?>" min="0" max="100" />
  <input type="hidden" name="cmd" value="vol" />
  <input type="submit" name="submit" data-mini="true" value="change" />
</div>
</form>
<h4>Position</h4>
<div data-role="controlgroup" data-type="horizontal">
<a class="json" data-role="button" data-icon="arrow-l" data-ajax="false" data-mini="true" data-iconpos="notext" href="json.php?cmd=rrew">Really Rewind</a>
<a class="json" data-role="button" data-icon="back" data-ajax="false" data-mini="true" data-iconpos="notext" href="json.php?cmd=rew">Rewind</a>
<a class="json" data-role="button" data-icon="forward" data-ajax="false" data-mini="true" data-iconpos="notext" href="json.php?cmd=fwd">Forward</a>
<a class="json" data-role="button" data-icon="arrow-r" data-ajax="false" data-mini="true" data-iconpos="notext" href="json.php?cmd=ffwd">Fast Forward</a>
</div>
<h4>Playback</h4>
<div data-role="controlgroup" data-type="horizontal">
<a class="json" data-icon="check" data-iconpos="notext" data-role="button" data-ajax="false" href="json.php?cmd=pause">Play/Pause</a>
<a class="json" data-icon="delete" data-iconpos="notext" data-role="button" data-ajax="false" href="json.php?cmd=stop">Stop</a>
</div>
<h4>Display</h4>
<div data-role="controlgroup" data-type="horizontal">
<a class="json" data-role="button" data-ajax="false" data-mini="true" href="json.php?cmd=osd">OSD</a>
<a class="json" data-role="button" data-ajax="false" data-mini="true" href="json.php?cmd=sub_select">Subs</a>
</div>
</div>
</div>




<div data-theme="a" data-role="panel" id="system" data-position="right"
data-display="overlay" data-dismissible="true">
<div class="panel-content">
<h4>TV</h4>
<div data-role="controlgroup" data-type="horizontal">
<a class="json" data-role="button" data-icon="gear" data-ajax="false" data-mini="true" data-iconpos="notext" href="json.php?cmd=tvon">TV On</a>
<a class="json" data-role="button" data-icon="delete" data-ajax="false" data-mini="true" data-iconpos="notext" href="json.php?cmd=tvoff">TV Off</a>
</div>
<h4>Ruh Roh</h4>
<div data-role="controlgroup" data-type="horizontal">
<a class="json" data-role="button" data-icon="refresh" data-ajax="false" data-mini="true" data-iconpos="notext" href="json.php?cmd=move">Move mplayer</a>
<a class="json" data-role="button" data-icon="alert" data-ajax="false" data-mini="true" data-iconpos="notext" href="json.php?cmd=reset">Reset</a>
</div>
<h4>Big Skip</h4>
<div data-role="controlgroup" data-type="horizontal">
<a class="json" data-role="button" data-icon="arrow-l" data-ajax="false" data-mini="true" data-iconpos="notext" href="json.php?cmd=rrrew">Really Really Rewind</a>
<a class="json" data-role="button" data-icon="arrow-r" data-ajax="false" data-mini="true" data-iconpos="notext" href="json.php?cmd=fffwd">Really Fast Forward</a>
</div>
</div>
</div>



<div data-role="header" class="header-bar">
<div data-role="controlgroup" data-type="horizontal" style="float:left">
<a data-role="button" data-icon="home" data-ajax="false" href="?path=/home/everyone/">home</a>
<a class="json" data-role="button" data-ajax="false" href="json.php?cmd=pause">Play/Pause</a>
<a class="json" data-role="button" data-ajax="false" href="json.php?cmd=stop">Stop</a>
</div>
<div align="right" data-role="controlgroup" data-type="horizontal">
<a data-role="button" data-icon="bars" data-iconpos="notext" href="#controls">Controls</a>
<a data-role="button" data-icon="star" data-iconpos="notext" href="#system">System</a>
</div>
</div>
<div data-theme="a" data-role="content">
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
  print("<ul data-filter-theme=\"a\" data-role=\"listview\" data-filter=\"true\">\n");
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

function talk_cute(href)
{
  $.getJSON(href, function(data){
    if(!data.result)
    {
      $("div[data-role='content']").replaceWith('failed...');
    }
  });
}

$(document).on('click', 'a.json', function(e){
  e.stopImmediatePropagation();
  e.preventDefault();
  talk_cute($(this).attr('href'));
  $(this).removeClass('ui-btn-active ui-focus');
  if($(this).hasClass('file'))
    $(this).closest('li').removeClass('ui-btn-active ui-focus');
});

$(document).on('submit', 'form', function(e){
  e.stopImmediatePropagation();
  e.preventDefault();
  talk_cute('json.php?' + $(this).serialize());
});

$(document).on('change', "input[type='range']", function(){
  alert('yay');
  $('form').trigger('submit');
  //$('#wtform').submit();
  //$(this).closest('form').trigger('submit');
});

$(document).on('swipeleft', function(e, ui){
  $("div[data-role='panel']").panel('open');
});

</script>
</body>
</html>
