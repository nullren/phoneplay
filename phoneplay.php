<html>
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
<a data-role="button" href="/">home</a>
<a data-role="button" href="?cmd=stop">stop</a>
<h3>play something</h3>
</div>
<div data-role="content">
<?php

$path = $_GET['path'];
$cmd = $_GET['cmd'];

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

/**
 * set up the fifos
 *
 * ~$ cd /home/everyone/
 * everyone$ mkdir movies
 * everyone$ cd movies/
 * movies$ mkfifo mplayer
 * movies$ mkfifo xset
 */

function send_mplayer_cmd($str)
{
  return send_fifo_cmd('/home/everyone/movies/mplayer', $str);
}

function send_xset_cmd($str)
{
  return send_fifo_cmd('/home/everyone/movies/xset', $str);
}

if ($cmd == 'stop')
{
  send_mplayer_cmd("stop");
  goto _LISTFILES;
}

if ($cmd == 'pause')
{
  send_mplayer_cmd("pause");
  print("<a class=\"play\" href=\"?cmd=play\">play</a>\n");
  goto _END;
}

if ($cmd == 'play')
{
  send_mplayer_cmd("pause");
  print("<a class=\"pause\" href=\"?cmd=pause\">pause</a>\n");
  goto _END;
}

# cmd empty, start a video
if (is_file($path))
{
  # turn screen on
  send_xset_cmd("wakeup");
  # play movie
  send_mplayer_cmd("loadfile \"$path\"");
  print("<a class=\"pause\" href=\"?path=".urlencode($path)."&cmd=pause\">pause</a>\n");
  goto _END;
}

_LISTFILES:

# default directory
if (!is_dir($path))
  $path = '/home/torrents/done';

if ($h = opendir($path))
{
  #print("<h6>$path</h6>\n");
  print("<ul data-role=\"listview\" data-filter=\"true\">\n");
  while (false !== ($entry = readdir($h))) {
    print("<li><a href=\"?path=".urlencode(realpath("$path/$entry"))."\">$entry</a></li>\n");
  }
  closedir($handle);
  print("</ul>\n");
  goto _END;
}

_ERROR:

print("there was an error or something...");
exit(1);

_END:
?>
</div>
</div>
</body>
</html>
