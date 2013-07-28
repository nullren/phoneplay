<?php

/**
 * use sessions to remember the last path so we can keep browsing
 * smoothly between things.
 */

define('MPLAYER_FIFO', '/home/everyone/movies/mplayer');
define('XSET_FIFO', '/home/everyone/movies/xset');
define('MOVIE_ROOT', '/home/torrents/done');

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
<div data-role="panel" id="controls" data-position="right" data-display="reveal" data-dismissible="true">
<div class="panel-content">
<h4>Volume:</h4>
<div data-role="controlgroup" data-type="horizontal">
<a data-role="button" data-icon="plus" data-ajax="false" data-mini="true" data-iconpos="notext" href="?cmd=volup">Volume Up</a>
<a data-role="button" data-icon="minus" data-ajax="false" data-mini="true" data-iconpos="notext" href="?cmd=voldown">Volume Down</a>
</div>
<h4>Position:</h4>
<div data-role="controlgroup" data-type="horizontal">
<a data-role="button" data-icon="arrow-l" data-ajax="false" data-mini="true" data-iconpos="notext" href="?cmd=rrew">Really Rewind</a>
<a data-role="button" data-icon="back" data-ajax="false" data-mini="true" data-iconpos="notext" href="?cmd=rew">Rewind</a>
<a data-role="button" data-icon="forward" data-ajax="false" data-mini="true" data-iconpos="notext" href="?cmd=fwd">Forward</a>
<a data-role="button" data-icon="arrow-r" data-ajax="false" data-mini="true" data-iconpos="notext" href="?cmd=ffwd">Fast Forward</a>
</div>
</div>
</div>
<div data-role="header" class="header-bar">
<div data-role="controlgroup" data-type="horizontal" style="float:left">
<a data-role="button" data-icon="home" data-ajax="false" href="?path=/home/torrents/done">home</a>
<a data-role="button" data-ajax="false" href="?cmd=pause">Play/Pause</a>
<a data-role="button" data-ajax="false" href="?cmd=stop">Stop</a>
</div>
<div align="right" data-role="controlgroup" data-type="horizontal">
<a data-role="button" data-icon="bars" data-iconpos="notext" href="#controls">Controls</a>
</div>
</div>
<div data-role="content">
<?php

/**
 * set up the fifos
 *
 * ~$ cd /home/everyone/
 * everyone$ mkdir movies
 * everyone$ cd movies/
 * movies$ mkfifo mplayer
 * movies$ mkfifo xset
 */

function send_fifo_cmd($fifo, $str)
{
  $f = fopen($fifo, "w");
  $o = fwrite($f, $str."\n");
  fclose($f);

  if ($o === FALSE)
  {
    //goto _ERROR;
    die("oh shit what the shit shit guys shit");
  }

  return $o;
}

function send_mplayer_cmd($str)
{
  return send_fifo_cmd(MPLAYER_FIFO, $str);
}

function send_xset_cmd($str)
{
  return send_fifo_cmd(XSET_FIFO, $str);
}

/**
 * these are the possible query string things. read them and do
 * something intelligent, hopefully.
 */

$path = NULL;
$cmd = NULL;

if (!empty($_GET['path']))
  $path = $_GET['path'];

if (empty($path) && !empty($_SESSION['path']))
  $path = $_SESSION['path'];

if (!empty($_GET['cmd']))
  $cmd = $_GET['cmd'];

if ($cmd == 'stop')
{
  send_mplayer_cmd("stop");
}

if ($cmd == 'pause')
{
  send_mplayer_cmd("pause");
}

if ($cmd == 'loadfile' && is_file($path))
{
  # turn screen on
  send_xset_cmd("wakeup");
  # play movie
  send_mplayer_cmd("loadfile \"$path\"");
  # change path to parent directory.
  $path = dirname($path);
}

/**
 * don't go below MOVIE_ROOT
 */

if (!is_dir($path) || join("/", array_slice(explode(DIRECTORY_SEPARATOR, $path),0, sizeof(explode(DIRECTORY_SEPARATOR,MOVIE_ROOT)))) != MOVIE_ROOT )
  $path = MOVIE_ROOT;

if ($h = opendir($path))
{
  #print("<h6>$path</h6>\n");
  print("<ul data-role=\"listview\" data-filter=\"true\">\n");
  while (false !== ($entry = readdir($h))) {
    print("<li><a data-transition=\"slide\" href=\"?cmd=loadfile&path=".urlencode(realpath("$path/$entry"))."\">$entry</a></li>\n");
  }
  closedir($h);
  print("</ul>\n");
}

$_SESSION['path'] = $path;

?>
</div>
</div>
</body>
</html>
