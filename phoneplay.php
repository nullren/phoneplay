<html>
<head>
<title>play something</title>
<link href="phoneplay.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="header">
<a href="?">home</a>
<a href="?cmd=stop">stop</a>
<hr />
</div>
<div id="content">
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
    goto ERROR;
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
  goto LISTFILES;
}

if ($cmd == 'pause')
{
  send_mplayer_cmd("pause");
  print("<a class=\"play\" href=\"?cmd=play\">play</a>\n");
  goto END;
}

if ($cmd == 'play')
{
  send_mplayer_cmd("pause");
  print("<a class=\"pause\" href=\"?cmd=pause\">pause</a>\n");
  goto END;
}

# cmd empty, start a video
if (is_file($path))
{
  # turn screen on
  send_xset_cmd("wakeup");
  # play movie
  send_mplayer_cmd("loadfile \"$path\"");
  print("<a class=\"pause\" href=\"?path=".urlencode($path)."&cmd=pause\">pause</a>\n");
  goto END;
}

LISTFILES:

# default directory
if (!is_dir($path))
  $path = '/home/torrents/done/today';

if ($h = opendir($path))
{
  print("<ul>\n");
  while (false !== ($entry = readdir($h))) {
    print("<li><a href=\"?path=".urlencode(realpath("$path/$entry"))."\">$entry</a></li>\n");
  }
  closedir($handle);
  print("</ul>\n");
  goto END;
}

ERROR:

print("there was an error or something...");
exit(1);

END:
?>
</div>
</body>
</html>
