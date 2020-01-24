<?php

include("phpgraphlib/phpgraphlib.php");

$ini_array = parse_ini_file("config.ini");
$results=$ini_array["results"];
$archives=$ini_array["archives"];


if(isset($ini_array["types"])) $types=$ini_array["types"];
if(isset($ini_array["cputestname"])) $cputests=$ini_array["cputestname"]; 
if(isset($ini_array["memtestname"])) $memtests=$ini_array["memtestname"]; 
if(isset($ini_array["javatestname"])) $javatests=$ini_array["javatestname"]; 
if(isset($ini_array["s3testname"])) $s3tests=$ini_array["s3testname"]; 
if(isset($ini_array["iotestname"])) $iotests=$ini_array["iotestname"]; 

if(!empty($types)) { 
  if (!empty($cputests)) movefiles($cputests,"cputests");
  if (!empty($memtests)) movefiles($memtests,"memtests");
  if (!empty($javatests)) movefiles($javatests,"javatests");
  if (!empty($s3tests)) movefiles($s3tests, "s3tests");
  if (!empty($iotests)) movefiles($iotests,"iotests");
}

function movefiles($tests, $testtype){
 global $archives;
 global $results;
 global $types;

 $cpuArray=array(); $memoryArray=array(); $fcacheArray=array(); $readtputArray=array();
 $writetputArray=array(); $readiopsArray=array(); $writeiopsArray=array(); $netrxtputArray=array();
 $nettxtputArray=array(); $netrxppsArray=array(); $nettxppsArray=array(); 

 $cpulegend=array(); $memorylegend=array(); $fcachelegend=array(); $readtputlegend=array();
 $writetputlegend=array(); $readiopslegend=array(); $writeiopslegend=array(); $netrxtputlegend=array();
 $nettxtputlegend=array(); $netrxppslegend=array(); $nettxppslegend=array();

 chdir ($results);
 foreach ($types as $type){
  foreach ($tests as $test) {
   exec("sudo mkdir -p $archives/compare-$type/$test/graphs");
   exec("ls -d $testtype-$test-*-$type-LATEST|egrep \"i3|i3en|r5|r5a|m5|m5a|c5|c5n\"", $dirs);
   if (empty($dirs)){ //ls: cannot access ..: No such file or directory
    print "dirs has no useful data\n";
    goto EMPTYDIR;
  }
   foreach ($dirs as $dir){
    if (! file_exists($dir)){
        goto NODATA;
     }
    chdir($dir);
    if (file_exists("metrics.csv")){
    $tmp=explode("$testtype-$test-",$dir);
    $ext=explode("-",$tmp[1]); // families of xlarge types: m5, m5a, r5, r5a
    exec ("sudo cp metrics.csv $archives/compare-$type/$test/graphs/$ext[0].csv");
    chdir("$archives/compare-$type/$test/graphs"); 
    $temp=explode(".", "$ext[0].csv");
    $instype=$temp[0];
    exec ("sed 1,1d $ext[0].csv > NoHeaders.csv");

    exec("cat NoHeaders.csv|awk -F ',' '{print $1}'|head -400|xargs|sed 's/ /,/g'>cpus.csv");
    $cpus=file_get_contents("cpus.csv", true);
    $cpus=array_map('intval', explode(',', $cpus));
    array_push($cpulegend, $instype);
    array_push($cpuArray, $cpus);

    exec("cat NoHeaders.csv|awk -F ',' '{print $2}'|head -400|xargs|sed 's/ /,/g'>memory.csv");
    $memory=file_get_contents("memory.csv", true);
    $memory=array_map('intval', explode(',', $memory));
    array_push($memorylegend, $instype);
    array_push($memoryArray, $memory);
    
    exec("cat NoHeaders.csv|awk -F ',' '{print $3}'|head -400|xargs|sed 's/ /,/g'>fcache.csv");
    $fcache=file_get_contents("fcache.csv", true);
    $fcache=array_map('intval', explode(',', $fcache));
    array_push($fcachelegend, $instype);
    array_push($fcacheArray, $fcache);

    exec("cat NoHeaders.csv|awk -F ',' '{print $4}'|head -400|xargs|sed 's/ /,/g'>readtput.csv");
    $readtput=file_get_contents("readtput.csv", true);
    $readtput=array_map('intval', explode(',', $readtput));
    array_push($readtputlegend, $instype);
    array_push($readtputArray, $readtput);

    exec("cat NoHeaders.csv|awk -F ',' '{print $5}'|head -400|xargs|sed 's/ /,/g'>writetput.csv");
    $writetput=file_get_contents("writetput.csv", true);
    $writetput=array_map('intval', explode(',', $writetput));
    array_push($writetputlegend, $instype);
    array_push($writetputArray, $writetput);

    exec("cat NoHeaders.csv|awk -F ',' '{print $6}'|head -400|xargs|sed 's/ /,/g'>readiops.csv");
    $readiops=file_get_contents("readiops.csv", true);
    $readiops=array_map('intval', explode(',', $readiops));
    array_push($readiopslegend, $instype);
    array_push($readiopsArray, $readiops);

    exec("cat NoHeaders.csv|awk -F ',' '{print $7}'|head -400|xargs|sed 's/ /,/g'>writeiops.csv");
    $writeiops=file_get_contents("writeiops.csv", true);
    $writeiops=array_map('intval', explode(',', $writeiops));
    array_push($writeiopslegend, $instype);
    array_push($writeiopsArray, $writeiops);


    exec("cat NoHeaders.csv|awk -F ',' '{print $8}'|head -400|xargs|sed 's/ /,/g'>netrxtput.csv");
    $netrxtput=file_get_contents("netrxtput.csv", true);
    $netrxtput=array_map('intval', explode(',', $netrxtput));
    array_push($netrxtputlegend, $instype);
    array_push($netrxtputArray, $netrxtput);

    exec("cat NoHeaders.csv|awk -F ',' '{print $9}'|head -400|xargs|sed 's/ /,/g'>nettxtput.csv");
    $nettxtput=file_get_contents("nettxtput.csv", true);
    $nettxtput=array_map('intval', explode(',', $nettxtput));
    array_push($nettxtputlegend, $instype);
    array_push($nettxtputArray, $nettxtput);

    exec("cat NoHeaders.csv|awk -F ',' '{print $10}'|head -400|xargs|sed 's/ /,/g'>netrxpps.csv");
    $netrxpps=file_get_contents("netrxpps.csv", true);
    $netrxpps=array_map('intval', explode(',', $netrxpps));
    array_push($netrxppslegend, $instype);
    array_push($netrxppsArray, $netrxpps);

    exec("cat NoHeaders.csv|awk -F ',' '{print $11}'|head -400|xargs|sed 's/ /,/g'>nettxpps.csv");
    $nettxpps=file_get_contents("nettxpps.csv", true);
    $nettxpps=array_map('intval', explode(',', $nettxpps));
    array_push($nettxppslegend, $instype);
    array_push($nettxppsArray, $nettxpps);
   }
NODATA:
   chdir($results);
  }//dir
  graphit($type,$test, "cpu","cpu usage (percent)",$cpuArray, $cpulegend);
  graphit($type,$test, "memory","memory used (GB) ",$memoryArray, $memorylegend);
  graphit($type,$test, "fcache","file system cache (GB)",$fcacheArray, $fcachelegend);
  graphit($type,$test, "readtput","Storage read tput (MB/s)",$readtputArray, $readtputlegend);
  graphit($type,$test, "writetput","Storage write tput (MB/s)",$writetputArray, $writetputlegend);
  graphit($type,$test, "readiops","Storage read iops",$readiopsArray, $readiopslegend);
  graphit($type,$test, "writeiops","storage write iops ",$writeiopsArray, $writeiopslegend);
  graphit($type,$test, "nettxtput","Net Tx bandwidth (Mbps)",$nettxtputArray, $nettxtputlegend);
  graphit($type,$test, "netrxtput","Net Rx bandwidth (Mbps)",$netrxtputArray, $netrxtputlegend);
  graphit($type,$test, "nettxpps","Net Tx PPS",$nettxppsArray, $nettxppslegend);
  graphit($type,$test, "netrxpps","Net Rx PPS",$netrxppsArray, $netrxppslegend); 

  chdir($results);
  $dirs="";

  $cpuArray=[]; $memoryArray=[]; $fcacheArray=[]; $readtputArray=[];
  $writetputArray=[]; $readiopsArray=[]; $writeiopsArray=[]; $netrxtputArray=[];
  $nettxtputArray=[]; $netrxppsArray=[]; $nettxppsArray=[]; 
   
  $cpulegend=[]; $memorylegend=[]; $fcachelegend=[]; $readtputlegend=[];
  $writetputlegend=[]; $readiopslegend=[]; $writeiopslegend=[]; $netrxtputlegend=[];
  $nettxtputlegend=[]; $netrxppslegend=[]; $nettxppslegend=[];
EMPTYDIR:
  chdir($results);
  $dirs="";
  }// next test
  chdir($results);
 }//next type
}//function

function graphit ($type, $test, $resource, $title, array $data, array $legends) {
  global $archives;
   
  $size=0;
  $leftover=0;
  chdir("$archives/compare-$type/$test/graphs");
  // data array has all the samples of the same test for all instances in the type.
  // Graph package only support 5 data sources. We need to create two graphs
  // number of families should not be more than 9.
  $size=sizeof($data);
  if (($size > 5) && ($size < 10 )){
    $graph = new PHPGraphLib(1600,600,"$resource-1.png");
    $leftover = $size%5;
    switch($leftover){
     // process 6-9 data source and store it as a seperate graph
      case 1:
      $graph->addData($data[5]);
      $graph->setLegendTitle($legends[5]);
      $graph->setLineColor('blue');
      break;
      case 2:
      $graph->addData($data[5],$data[6]);
      $graph->setLegendTitle($legends[5],$legends[6]); 
      $graph->setLineColor('blue','green');
      break;
      case 3:
      $graph->addData($data[5],$data[6],$data[7]);
      $graph->setLegendTitle($legends[5],$legends[6],$legends[7]);
      $graph->setLineColor('blue','green','olive');
      break;
      case 4:
      $graph->addData($data[5],$data[6],$data[7],$data[8]);
      $graph->setLegendTitle($legends[5],$legends[6],$legends[7],$legends[8]);
      $graph->setLineColor('blue','green','olive','maroon');
      break;
   }//switch
    $graph->setBackgroundColor("white");
    $graph->setTextColor('green');
    $graph->setLegend(true);
    $graph->setLegendTextColor('black');
    $graph->setSwatchOutlineColor('white');
    $graph->setLegendColor('white');
    $graph->setupYAxis(8, 'black');
    $graph->setupXAxis(true);
    $graph->setXValues(false);
    $graph->setupXAxis(8, 'black');
    //$graph->setTitle("$title $unit");
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
    //$graph->setGoalLine($goal);
    $graph->setGoalLineColor('red');
    $graph->createGraph();
  }//if
  // Now process first five data sources
 $graph = new PHPGraphLib(1600,600,"$resource.png");
 $size=$size-$leftover;
 switch($size){
    case 1:
    $graph->addData($data[0]);
    $graph->setLegendTitle($legends[0]);
    $graph->setLineColor('blue');
    break;
    case 2:
    $graph->addData($data[0],$data[1]);
    $graph->setLegendTitle($legends[0],$legends[1]);
    $graph->setLineColor('blue','green');
    break;
    case 3:
    $graph->addData($data[0],$data[1],$data[2]);
    $graph->setLegendTitle($legends[0],$legends[1],$legends[2]);
    $graph->setLineColor('blue','green','olive');
    break;
    case 4:
    $graph->addData($data[0],$data[1],$data[2],$data[3]);
    $graph->setLegendTitle($legends[0],$legends[1],$legends[2], $legends[3]);
    $graph->setLineColor('blue','green','olive','maroon');
    break;
    case 5:
    $graph->addData($data[0],$data[1],$data[2],$data[3],$data[4]);
    $graph->setLegendTitle($legends[0],$legends[1],$legends[2],$legends[3],$legends[4]);
    $graph->setLineColor('blue','green','olive','maroon','gray');
    break;
  }//switch <= 5 
    $graph->setBackgroundColor("white");
    $graph->setTextColor('green');
    $graph->setLegend(true);
    $graph->setLegendTextColor('black');
    $graph->setSwatchOutlineColor('white');
    $graph->setLegendColor('white');
    $graph->setupYAxis(8, 'black');
    $graph->setupXAxis(true);
    $graph->setXValues(false);
    $graph->setupXAxis(8, 'black');
    //$graph->setTitle("$title $unit");
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
    //$graph->setGoalLine($goal);
    $graph->setGoalLineColor('red');
    $graph->createGraph();
}//functionn
?>
