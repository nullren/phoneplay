<?php

/**
 * use sessions to remember the last path so we can keep browsing
 * smoothly between things.
 */

define('MPLAYER_FIFO', '/home/everyone/movies/mplayer');
define('XSET_FIFO', '/home/everyone/movies/xset');

session_start();

?><html>
<head>
<title>play something</title>
<meta name = "viewport"  content = "initial-scale=1.0, user-scalable=no, width=device-width">
<link rel="stylesheet" href="http://code.jquery.com/mobile/1.2.1/jquery.mobile-1.2.1.min.css" />
<script src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
<script src="http://code.jquery.com/mobile/1.2.1/jquery.mobile-1.2.1.min.js"></script>
</head>
<body>
<div data-role="page">
<div data-role="header">
<div data-role="controlgroup" data-type="horizontal">
<a data-role="button" data-icon="home" href="?path=/home/torrents/done">home</a>
<a data-role="button" data-inline="true" href="?cmd=pause">Play/Pause</a>
<a data-role="button" data-inline="true" href="?cmd=stop">Stop</a>
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

# default directory
if (!is_dir($path))
  $path = '/home/torrents/done';

if ($h = opendir($path))
{
  #print("<h6>$path</h6>\n");
  print("<ul data-role=\"listview\" data-filter=\"true\">\n");
  while (false !== ($entry = readdir($h))) {
    print("<li><a href=\"?cmd=loadfile&path=".urlencode(realpath("$path/$entry"))."\">$entry</a></li>\n");
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
