#!/bin/sh

MP_FIFO=/home/everyone/movies/mplayer
XS_FIFO=/home/everyone/movies/xset

DISPLAY=:0.1 mplayer -fs -ao pulse::1 -af volnorm -slave -input file=$MP_FIFO -idle &

while true
do
  exec <$XS_FIFO
  read line

  DISPLAY=:0 /usr/bin/xset dpms force on
done
