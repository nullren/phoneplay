<?php

/**
 * if NO_FIFO is set to true, then disregard any fifo stuff. this is
 * useful for testing the web app without having a functioning mplayer
 * daemon running.
 */
define('NO_FIFO',FALSE);
define('MPLAYER_FIFO', '/home/everyone/movies/mplayer');
define('XSET_FIFO', '/home/everyone/movies/xset');

session_start();

/**
 * the json return packet contains two things: result True or False,
 * basically, False denotes there was an error. then return contains
 * the payload.
 */
header('Content-Type: application/json');

/**
 * this function is what is going to give the jquery nonsense
 * something to look at. set the result to TRUE or FALSE, FALSE
 * indicates an error. payload then contains whatever content we want
 * to send.
 */
function spit_j($r, $payload)
{
  print(json_encode(array('result'=>$r,'return'=>$payload)));
  exit(0);
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
function send_fifo_cmd($fifo, $str)
{
  if (NO_FIFO)
    return NO_FIFO;

  $f = fopen($fifo, "w");
  $o = fwrite($f, $str."\n");
  fclose($f);

  if ($o === FALSE)
  {
    spit_j($o,"failed to write to fifo '$fifo'");
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

switch ($cmd) {
  case 'volup':
    send_mplayer_cmd("volume +5");
    $_SESSION['vol'] += 5;
    break;

  case 'voldown':
    send_mplayer_cmd("volume -5");
    $_SESSION['vol'] -= 5;
    break;

  case 'vol':
    if (!empty($_GET['vol'])){
      send_mplayer_cmd("volume ".$_GET['vol'].' 1');
      $_SESSION['vol'] = $_GET['vol'];
    }
    break;

  case 'sub_select':
    send_mplayer_cmd("sub_select");
    break;

  case 'osd':
    send_mplayer_cmd("osd");
    break;

  case 'stop':
    send_mplayer_cmd("stop");
    break;

  case 'fwd':
    send_mplayer_cmd("seek +10 0");
    break;

  case 'ffwd':
    send_mplayer_cmd("seek +60 0");
    break;

  case 'rew':
    send_mplayer_cmd("seek -10 0");
    break;

  case 'rrew':
    send_mplayer_cmd("seek -60 0");
    break;

  case 'pause':
    send_mplayer_cmd("pause");
    break;

  case 'loadfile':
    if (is_file($path))
    {
      # turn screen on
      send_xset_cmd("wakeup");
      # play movie
      send_mplayer_cmd("loadfile \"$path\"");
      # change path to parent directory.
    }
    break;
}

spit_j(TRUE, 'ok'); // this kills the batman
