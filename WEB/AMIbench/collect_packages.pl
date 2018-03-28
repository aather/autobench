#! /usr/bin/perl

#use warnings;
#use strict;
use Fcntl qw/:flock/;
$uname=`uname -r`;
#open SELF, "< $0" or die ;
#flock SELF, LOCK_EX | LOCK_NB  or die "Another instance of the same program is already running: $!";
print "Kernel Version:$uname";
open(MPSTAT, "sudo dpkg --list |")|| die print "failed to get data: $!\n";
 while (<MPSTAT>) {
 next if (/^$/ || /^Desired/ || /Status=/ || /Err?=/ || /Name/ || /^===/) ;
 if ( /^ii/ ) {
 @stats = split;
 print "$stats[1]:$stats[2]\n" ;
 }
}
