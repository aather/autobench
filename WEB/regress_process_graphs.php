<?php

include("phpgraphlib/phpgraphlib.php");

$ini_array = parse_ini_file("config.ini");
$results=$ini_array["results"];


if(isset($ini_array["inst"])) $inst=$ini_array["inst"];
if(isset($ini_array["cputestname"])) $cputests=$ini_array["cputestname"]; 
if(isset($ini_array["memtestname"])) $memtests=$ini_array["memtestname"]; 
if(isset($ini_array["javatestname"])) $javatests=$ini_array["javatestname"]; 
if(isset($ini_array["s3testname"])) $s3tests=$ini_array["s3testname"]; 
if(isset($ini_array["iotestname"])) $iotests=$ini_array["iotestname"]; 

if(!empty($inst)) { 
  if (!empty($cputests)) movefiles($cputests,"cputests");
  if (!empty($memtests)) movefiles($memtests,"memtests");
  if (!empty($javatests)) movefiles($javatests,"javatests");
  if (!empty($s3tests)) movefiles($s3tests, "s3tests");
  if (!empty($iotests)) movefiles($iotests,"iotests");
}

function movefiles($tests, $testtype){
 global $results;
 global $inst;

 chdir ($results);
 foreach ($inst as $ins){
  foreach ($tests as $test) {
   exec("ls -dtr $testtype-$test-$ins-*-*", $dirs);
   if (empty($dirs)){
    print "dirs has no useful data\n";
    goto EMPTYDATA;
   }
   foreach ($dirs as $dir){
   if (! file_exists($dir)){
        goto NODATA;
     }
    //print "$dir\n";
    chdir($dir);
    if (!file_exists("graphs")){
     if (file_exists("metrics.csv")){
      exec("sudo mkdir graphs");
      exec("sudo cp metrics.csv graphs/.");
      $tmp=explode("$testtype-$test-",$dir);
      $ext=explode("-",$tmp[1]);
      $instype=$ext[0]."-".$ext[1];   // r5-xlarge ...

      chdir("graphs");
      exec ("sed 1,1d metrics.csv > NoHeaders.csv");

    exec("cat NoHeaders.csv|awk -F ',' '{print $1}'|head -400|xargs|sed 's/ /,/g'>cpus.csv");
    $cpus=file_get_contents("cpus.csv", true);
    $cpus=array_map('intval', explode(',', $cpus));
    graphit($dir,$test,"CPU usage (percent)","cpu",$cpus,$instype );

    exec("cat NoHeaders.csv|awk -F ',' '{print $2}'|head -400|xargs|sed 's/ /,/g'>memory.csv");
    $memory=file_get_contents("memory.csv", true);
    $memory=array_map('intval', explode(',', $memory));
    graphit($dir,$test,"memory used (GB)","memory",$memory,$instype );

    exec("cat NoHeaders.csv|awk -F ',' '{print $3}'|head -400|xargs|sed 's/ /,/g'>fcache.csv");
    $fcache=file_get_contents("fcache.csv", true);
    $fcache=array_map('intval', explode(',', $fcache));
    graphit($dir,$test,"file system cache (GB)","fcache",$fcache,$instype );

    exec("cat NoHeaders.csv|awk -F ',' '{print $4}'|head -400|xargs|sed 's/ /,/g'>readtput.csv");
    $readtput=file_get_contents("readtput.csv", true);
    $readtput=array_map('intval', explode(',', $readtput));
    graphit($dir,$test,"storage read tput (MB/s)","readtput",$readtput,$instype );

    exec("cat NoHeaders.csv|awk -F ',' '{print $5}'|head -400|xargs|sed 's/ /,/g'>writetput.csv");
    $writetput=file_get_contents("writetput.csv", true);
    $writetput=array_map('intval', explode(',', $writetput));
    graphit($dir,$test,"storage write tput (MB/s)","writetput",$writetput,$instype );

    exec("cat NoHeaders.csv|awk -F ',' '{print $6}'|head -400|xargs|sed 's/ /,/g'>readiops.csv");
    $readiops=file_get_contents("readiops.csv", true);
    $readiops=array_map('intval', explode(',', $readiops));
    graphit($dir,$test,"storage read iops","readiops",$readiops,$instype );

    exec("cat NoHeaders.csv|awk -F ',' '{print $7}'|head -400|xargs|sed 's/ /,/g'>writeiops.csv");
    $writeiops=file_get_contents("writeiops.csv", true);
    $writeiops=array_map('intval', explode(',', $writeiops));
    graphit($dir,$test,"storage write iops","writeiops",$writeiops,$instype );

    exec("cat NoHeaders.csv|awk -F ',' '{print $8}'|head -400|xargs|sed 's/ /,/g'>netrxtput.csv");
    $netrxtput=file_get_contents("netrxtput.csv", true);
    $netrxtput=array_map('intval', explode(',', $netrxtput));
    graphit($dir,$test,"Net Rx tput (Mbps)","netrxtput",$netrxtput,$instype );

    exec("cat NoHeaders.csv|awk -F ',' '{print $9}'|head -400|xargs|sed 's/ /,/g'>nettxtput.csv");
    $nettxtput=file_get_contents("nettxtput.csv", true);
    $nettxtput=array_map('intval', explode(',', $nettxtput));
    graphit($dir,$test,"Net Tx tput (Mbps)","nettxtput",$nettxtput,$instype );

    exec("cat NoHeaders.csv|awk -F ',' '{print $10}'|head -400|xargs|sed 's/ /,/g'>netrxpps.csv");
    $netrxpps=file_get_contents("netrxpps.csv", true);
    $netrxpps=array_map('intval', explode(',', $netrxpps));
    graphit($dir,$test,"Net Rx PPS","netrxpps",$netrxpps,$instype );

    exec("cat NoHeaders.csv|awk -F ',' '{print $11}'|head -400|xargs|sed 's/ /,/g'>nettxpps.csv");
    $nettxpps=file_get_contents("nettxpps.csv", true);
    $nettxpps=array_map('intval', explode(',', $nettxpps));
    graphit($dir,$test,"Net Tx PPS","nettxpps",$nettxpps,$instype );
    }//if metrics.csv exists
   }// if graphs dir exists
NODATA:
   chdir($results);
  }//dir
  chdir($results);
  $dirs="";
  }// next test
EMPTYDATA:
  chdir($results);
 }//next inst
}//function

function graphit ($dir, $test, $title, $resource, $data, $legend) {
  global $results;

  chdir($results);
  chdir("$dir/graphs");
  $graph = new PHPGraphLib(1600,600,"$resource.png");
  $graph->addData($data);
  $graph->setBackgroundColor("white");
  $graph->setTextColor('green');
  $graph->setLegend(true);
  $graph->setLegendTitle($legend);
  $graph->setLineColor('blue');
  $graph->setLegendTextColor('black');
  $graph->setSwatchOutlineColor('white');
  $graph->setLegendColor('white');
  $graph->setupYAxis(8, 'black');
  $graph->setupXAxis(true);
  $graph->setXValues(false);
  $graph->setupXAxis(8, 'black');
  $graph->setTitle("$test $title");
  $graph->setTitleColor('black');
  $graph->setGrid(false);
  $graph->setGradient('blue', 'silver');
  $graph->setBars(false);
  $graph->setLine(true);
  $graph->setDataPoints(false);
  $graph->setDataPointColor('green');
  $graph->setDataValues(false);
  $graph->setDataValueColor('red');
  $graph->setGoalLineColor('red');
  $graph->createGraph();
}//function
?>
