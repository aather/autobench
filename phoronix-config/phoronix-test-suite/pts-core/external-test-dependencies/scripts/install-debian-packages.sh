#!/bin/sh
# Debian, unlike Ubuntu, doesn't sudo it users by default
if [ -x /usr/bin/aptitude ]; then
	# aptitude is nice since it doesn't fail if a non-existant package is hit
	# See: http://bugs.debian.org/cgi-bin/bugreport.cgi?bug=503215
	su -c "aptitude -y install $*"
else
	su -c "apt-get -y --ignore-missing install $*"
fi
