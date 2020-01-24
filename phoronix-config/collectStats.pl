#! /usr/bin/perl
#version 1.2

sub cpustats;
sub memstats;
sub iostats;
sub netstats;
sub capacity;
sub fstype;
sub signal_handler;

$SIG{INT} = \&signal_handler;
$SIG{TERM} = \&signal_handler;

@cpumetrics=();
@memorymetrics=();
@fsmetrics=();
@readtputmetrics=();
@writetputmetrics=();
@readiopsmetrics=();
@writeiopsmetrics=();
@rxbytesmetrics=();
@txbytesmetrics=();
@rxppsmetrics=();
@txppsmetrics=();

$directory=$ARGV[0];
$systats=$ARGV[1];
#$interval=$ARGV[2];
$interval=1;
$iterations=10;

$cpudelta=0;
$iodelta=0;      
$netdelta=0;      

$initcpu=0;
$totcpuinit=0;
$fullcpu=0;
$totcpu=0;

#$ext = `date -u '+%Y-%m-%S'`;                 # file extension
#$ext = join('',split(/\n/,$ext));

# Dump metrics into csv file
open(FH, '>>', "/$directory/$systats") or die $!;
print FH "cpu usage(percent),";
print FH "memory used(GB),";
print FH "file system cache(GB),";
print FH "storage read tput(MB/s),";
print FH "storage write tput(MB/s),";
print FH "storage read iops,";
print FH "storage write iops,";
print FH "network ingress bandwidth(Mbps),";
print FH "network egress bandwidth(Mbps),";
print FH "network ingress pps,";
print FH "network egress pps\n";

while(1){
 $loops=$iterations;
   while($loops-- > 0){
    cpustats();
    memstats();
    iostats();
    netstats();

    sleep ($interval);
   }
 $size=(@cpumetrics);
 $k=0;

  while ($k < $size){
     print FH $cpumetrics[$k];
     print FH ",";
     print FH $memorymetrics[$k];
     print FH ",";
     print FH $fsmetrics[$k];
     print FH ",";
     print FH $readtputmetrics[$k];
     print FH ",";
     print FH $writetputmetrics[$k];
     print FH ",";
     print FH $readiopsmetrics[$k];
     print FH ",";
     print FH $writeiopsmetrics[$k];
     print FH ",";
     print FH $rxbytesmetrics[$k];
     print FH ",";
     print FH $txbytesmetrics[$k];
     print FH ",";
     print FH $rxppsmetrics[$k];
     print FH ",";
     print FH $txppsmetrics[$k];
     print FH "\n";
     $k++;
 }
@cpumetrics=();
@memorymetrics=();
@fsmetrics=();
@readtputmetrics=();
@writetputmetrics=();
@readiopsmetrics=();
@writeiopsmetrics=();
@rxbytesmetrics=();
@txbytesmetrics=();
@rxppsmetrics=();
@txppsmetrics=();
}

# Ship it to S3
#my @args = ("s3cp", "/tmp/systats.csv", "s3://nflx.cldperf.test/ML/$cluster/systats.csv");
#system(@args) == 0 or die " system @args failed; $?";

sub signal_handler {
  close(FH);
  die "clean up $!";
}

sub cpustats() {
open (CPUSTATS, "head -1 /proc/stat|")|| die print "failed to get data: $!\n";
while (<CPUSTATS>){
   next if (/^$/ || /^intr/ || /^btime/ || /^processes/ || /^softirq/) ;
  if ($cpudelta == 0){  #only for first iteration
   s/cpu/0/;  # substitute cpu with 0
   @stats = split;
   foreach my $combine (@stats){   # add all stats
       $totcpuinit = $totcpuinit + $combine;
   }
  $initcpu = $totcpuinit - $stats[4] - $stats[5]; # substract cpu idle time
  }
 else{  # second and onward iterations
   s/cpu/0/;  # substitute cpu with 0
   @stats = split;
   foreach my $combine (@stats){   # add all stats
       $totcpu = $totcpu + $combine;
   }
   $fullcpu = $totcpu - $stats[4] - $stats[5]; # substract cpu idle time

   $temp1 = $fullcpu;
   $temp2 = $totcpu;

   $fullcpu = $fullcpu - $initcpu;   # delta from previous sample
   $totcpu = $totcpu - $totcpuinit;

   $cpu= ($fullcpu/$totcpu) * 100;   # convert into percentage
   $cpu= sprintf("%0.2f",$cpu);
   $initcpu = $temp1;
   $totcpuinit= $temp2;
  
   push (@cpumetrics,$cpu);
 }
 $totcpu=0;
 }
 $cpudelta=1;
}

sub memstats(){
my @temp ;
open (MEMSTATS,"head -5 /proc/meminfo|") || die print "failed to get data: $!\n";
while (<MEMSTATS>){
  @stats = split;
  push (@temp, $stats[1]);
 }

 $free_unused = $temp[1];
 $free_cached = $temp[3] + $temp[4];
 $memused = $temp[0] - $free_unused - $free_cached; # used application memory 
 $memused = $memused / 1024 ;       
 $memused = $memused / 1024 ;    # GB  
 $memused = sprintf("%0.2f",$memused);

 $free_cached = $free_cached / 1024;
 $free_cached = $free_cached / 1024; # GB
 $free_cached = sprintf("%0.2f",$free_cached);
 
 push (@memorymetrics,$memused);
 push (@fsmetrics,$free_cached);
}

sub iostats(){
my $temp;
$readtput=0; $writetput=0; $readiops=0; $writeiops=0;
open (IOSTATS, "cat /proc/diskstats|") || die print "failed to get data: $!\n";
while (<IOSTATS>){
 next if (/^$/ || /loop/ || /ram/ || /md/) ;
 if ($iodelta == 0){  # only for first iteration
  @stats = split;  
  $initreadtput = $stats[5] + $initreadtput;
  $initwritetput = $stats[9] + $initwritetput;

  $initreadiops = $stats[3] + $initreadiops;
  $initwriteiops = $stats[7] + $initwriteiops;
 }
 else {
  @stats= split;

  $readtput=  $stats[5] + $readtput;
  $writetput=  $stats[9] + $writetput;
  $readiops=  $stats[3] + $readiops;
  $writeiops=  $stats[7] + $writeiops;
  }
 } #while
 if ($iodelta == 1){   #calculate in second and onward iterations
  $temp = $readtput;
  $readtput = $readtput - $initreadtput;   # delta from previous sample
  $readtput = (($readtput * 512 ) / 1024) / 1024;
  $readtput = $readtput / $interval;
  $readtput = sprintf("%0.2f",$readtput);

  $initreadtput = $temp;
  push(@readtputmetrics,$readtput);

  $temp = $writetput;
  $writetput = $writetput - $initwritetput;   # delta from previous sample
  $writetput = (($writetput * 512 ) / 1024) / 1024;
  $writetput = $writetput / $interval;
  $writetput = sprintf("%0.2f",$writetput);

  $initwritetput = $temp;
  push(@writetputmetrics,$writetput);

  $temp = $readiops;
  $readiops = $readiops - $initreadiops;   # delta from previous sample
  $readiops = $readiops / $interval;

  $initreadiops = $temp;
  push(@readiopsmetrics,$readiops);

  $temp = $writeiops;
  $writeiops = $writeiops - $initwriteiops;   # delta from previous sample
  $writeiops = $writeiops / $interval;

  $initwriteiops = $temp;
  push(@writeiopsmetrics,$writeiops);

 }
$iodelta=1;
}

sub netstats(){
$rxbytes=0; $txbytes=0; $rxpps=0; $txpps=0;
open (NETSTATS, "cat /proc/net/dev|" )|| die print "failed to get data: $!\n";
while (<NETSTATS>){
  next if (/^$/ || /^Inter/ || /face/ || /lo:/) ;
  if ($netdelta == 0){  #only for first iteration
  @stats = split;
  $initrxbytes = $stats[1] + $initrxbytes;
  $inittxbytes = $stats[9] + $inittxbytes;
  $initrxpps = $stats[2] + $initrxpps;
  $inittxpps = $stats[10] + $inittxpps;
 }
 else {
  @stats = split;
  $rxbytes=  $stats[1] + $rxbytes;
  $txbytes=  $stats[9] + $txbytes;
  $rxpps=  $stats[2] + $rxpps;
  $txpps=  $stats[10] + $txpps;

 }
}
  if ($netdelta == 1){
    $temp = $rxbytes;
    $rxbytes = $rxbytes - $initrxbytes;   # delta from previous sample
    $rxbytes = (($rxbytes * 8) / 1024) / 1024 ;
    $rxbytes = $rxbytes / $interval ;   #GB/s
    $rxbytes =  sprintf("%0.2f",$rxbytes);
    $initrxbytes = $temp;
    push (@rxbytesmetrics,$rxbytes);
   
    $temp = $txbytes;
    $txbytes = $txbytes - $inittxbytes;   # delta from previous sample
    $txbytes = (($txbytes * 8) / 1024) / 1024 ;
    $txbytes = $txbytes / $interval ;   #GB/s
    $txbytes =  sprintf("%0.2f",$txbytes);
    $inittxbytes = $temp;
    push (@txbytesmetrics,$txbytes);
    
    $temp = $rxpps;
    $rxpps = $rxpps - $initrxpps;   # delta from previous sample
    $rxpps = $rxpps / $interval ;   #GB/s
    $initrxpps = $temp;
    push (@rxppsmetrics,$rxpps);

    $temp = $txpps;
    $txpps = $txpps - $inittxpps;   # delta from previous sample
    $txpps = $txpps / $interval ;   #GB/s
    $inittxpps = $temp;
    push (@txppsmetrics,$txpps);

 }
$netdelta = 1;
}

