#!/bin/sh

DISPLAY=:0.1 mplayer -fs -ao pulse::1 -af volnorm -slave -input file=/home/everyone/movies/mplayer -idle
