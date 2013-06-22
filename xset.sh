#!/bin/sh

while true
do
  exec </home/everyone/movies/xset
  read line

  DISPLAY=:0 /usr/bin/xset dpms force on
done
