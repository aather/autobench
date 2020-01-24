<?php

include("phpgraphlib/phpgraphlib.php");

$ini_array = parse_ini_file("config.ini");
$results=$ini_array["results"];
chdir($results);
$archives=$ini_array["archives"];
$WEB=$ini_array["resulturl"];
exec("sudo find . -type d -empty -delete");
exec("sudo rm -rf $archives/*");
//Options set in config file
if(isset($ini_array["family"])) $families=$ini_array["family"];
if(isset($ini_array["types"])) $types=$ini_array["types"];
if(isset($ini_array["inst"])) $inst=$ini_array["inst"];
//Benchmarks set in config file
if(isset($ini_array["cputestname"])) {
  $cputests=$ini_array["cputestname"];
  $cputeststrings=$ini_array["cputestversion"];
}
if(isset($ini_array["memtestname"])) {
   $memtests=$ini_array["memtestname"];
   $memteststrings=$ini_array["memtestversion"];
}
if(isset($ini_array["iotestname"])) {
   $iotests=$ini_array["iotestname"];
   $ioteststrings=$ini_array["iotestversion"];
}
if(isset($ini_array["javatestname"])){
   $javatests=$ini_array["javatestname"];
   $javateststrings=$ini_array["javatestversion"];
}
if(isset($ini_array["s3testname"])){
   $s3tests=$ini_array["s3testname"];
   $s3teststrings=$ini_array["s3testversion"];
}
// process json results
//instance families
if (!empty($families)){
   if (!empty($cputests)){
     processjson($families, "family","cputests");
   }
   if (!empty($memtests)){
     processjson($families, "family","memtests");
   }
   if (!empty($javatests)){
     processjson($families, "family","javatests");
   }
   if (!empty($iotests)){
     processjson($families, "family","iotests");
   }
   if (!empty($s3tests)){
     processjson($families, "family","s3tests");
   }
}
// instance types
if (!empty($types)){
   if (!empty($cputests)){
     processjson($types, "type","cputests");
   }
   if (!empty($memtests)){
     processjson($types, "type","memtests");
   }
   if (!empty($javatests)){
     processjson($types, "type","javatests");
   }
   if (!empty($iotests)){
     processjson($types, "type","iotests");
   }
   if (!empty($s3tests)){
     processjson($types, "type","s3tests");
   }
}
//all instances
if (!empty($inst)){
   if (!empty($cputests)){
     processjson($inst, "inst","cputests");
   }
   if (!empty($memtests)){
     processjson($inst, "inst","memtests");
   }
   if (!empty($javatests)){
     processjson($inst, "inst","javatests");
   }
   if (!empty($iotests)){
     processjson($inst, "inst","iotests");
   }
   if (!empty($s3tests)){
     processjson($inst, "inst","s3tests");
   }
}

exec("sudo chmod -R 777 $archives/*");

function processjson($names, $what, $testtype){
 global $archives;
 global $results;
 global $families;
 global $WEB;
 global $cputests, $memtests, $javatests, $iotests, $s3tests;
 global $cputeststrings, $memteststrings, $javateststrings, $ioteststrings, $s3teststrings;
 if (preg_match("/cputests/", "$testtype")) {
	$mytests = $cputests;
	$myteststrings = $cputeststrings;
 }
 if (preg_match("/memtests/", "$testtype")) {
	$mytests = $memtests;
	$myteststrings = $memteststrings;
 }
 if (preg_match("/iotests/", "$testtype")) {
	$mytests = $iotests;
	$myteststrings = $ioteststrings;
 }
 if (preg_match("/javatests/", "$testtype")) {
	$mytests = $javatests;
        $myteststrings = $javateststrings;

  }
 if (preg_match("/s3tests/", "$testtype")) {
	$mytests = $s3tests;
	$myteststrings = $s3teststrings;
  }
// Start processing all tests for the family, type and inst
 foreach ($names as $name){
   //print "$name\n";
   $i=0;  //teststrings array
   foreach ($mytests as $test){
    //print "$test\n";
    //print "$what\n";
    if ($what == "inst") {
         exec("sudo mkdir -p $archives/regression-$name/$test");

         $report = "$archives/regression-$name/$test/report";
         $handle = fopen($report, 'a') or die('file open failed: ' .$report);

         $csvreport  = "$archives/regression-$name/$test/csvreport";
         $handlecsv  = fopen($csvreport, 'a') or die('file open failed: ' .$csvreport);

         exec("ls -dtr $testtype-$test-$name-*-*", $dirs);  
    }
    elseif (($what == "family") || ($what == "type")){ 
        exec("sudo mkdir -p $archives/compare-$name/$test");
        $report = "$archives/compare-$name/$test/report";
        $handle = fopen($report, 'a') or die('file open failed: ' .$report);

        $csvreport  = "$archives/compare-$name/$test/csvreport";
        $handlecsv = fopen($csvreport, 'a') or die('file open failed: ' .$csvreport);

	if ($what == "family")  exec("ls -d $testtype-$test-$name-*-LATEST", $dirs);
       //if ($what == "type")    exec("ls -d $testtype-$test-*-$name-LATEST|", $dirs); 
       //if ($what == "type")    exec("ls -d $testtype-$test-*-$name-LATEST|egrep \"i3|i3en|r5|r5a|m5|m5a|c5|c5n\"", $dirs); 
       if ($what == "type")    exec("ls -d $testtype-$test-*-$name-LATEST|egrep -v \"titus|r4|m4|t3\"", $dirs);
    }
  if (empty($dirs)){
    print "dirs has no useful data\n";
    goto EMPTYDIR;
  }
  foreach ($dirs as $dir){
     if (! file_exists($dir)){
        goto NODATA;
     }
     print "$dir\n";

     chdir($dir);
     if (file_exists("combine.json") && filesize("combine.json")){ //file exists and not zero
      $string = file_get_contents("combine.json");
     }
     elseif (file_exists("result.json") && filesize("result.json")){
      $string = file_get_contents("result.json");
     }
     else {
	print "No $dir/result.json found..\n";
        chdir ($results);
        exec("sudo rm -rf $dir");
        goto NODATA;
     }
      $data = json_decode($string);
      $teststring = $myteststrings[$i];
      if (file_exists("system-logs")){
       chdir("system-logs");
       exec("ls -d *", $versions);
       foreach ($versions as $ver){
         $machine = $ver;
        }
       chdir($results);
       chdir($dir);
      }
      else { 
       print "NO system-logs dir\n";
       chdir ($results);
       exec("sudo rm -rf $dir");
       goto NODATA; 
       }
     $pattern = explode('/',$teststring);
     if (file_exists("combine.json") && filesize("combine.json")){ //file exists and not zero
      if (preg_match("/$pattern[1]/",$string,$matches)){
         $title= $data[0]->{'title'};
         $arguments = $data[0]->{'results'}->{$teststring}->arguments;
         $units = $data[0]->{'results'}->{$teststring}->units;
         $value = $data[0]->{'results'}->{$teststring}->{'results'}->{$machine}->value;
         $date =  $data[1]->{'date'};
         $ami =  $data[1]->{'ami'};
         $amiVersion =  $data[1]->{'version'};
         //$buildDate = $data[1]->{'buildDate'};
         $javaVersion = $data[1]->{'javaVersion'};
       }
      }
     elseif (file_exists("result.json") && filesize("result.json")){
       if (preg_match("/$pattern[1]/",$string,$matches)){
          $title= $data->{'title'};
          $arguments = $data->{'results'}->{$teststring}->arguments;
          $units = $data->{'results'}->{$teststring}->units;
          $value = $data->{'results'}->{$teststring}->{'results'}->{$machine}->value; 
       }
      }
     if (file_exists("combine.json")){
      fwrite($handle,"$date;");
      fwrite($handle,"$ami;");
      fwrite($handle,"$amiVersion;");
      //fwrite($handle,"$buildDate;");
      fwrite($handle,"$javaVersion;");
      fwrite($handle,"$title;");
      fwrite($handle,"$arguments;");
      fwrite($handle,"$units;");
      fwrite($handle,"$value;");
      if (file_exists("$results/$dir/graphs") && ($what == "inst")){
        fwrite($handle,
               "<a target=\"_blank\" href=\"http://autobench.test.netflix.net/$WEB/$dir/graphs/cpu.png\"><font color=blue>cpu</font></a> &nbsp <a target=\"_blank\" href=\"http://autobench.test.netflix.net/$WEB/$dir/graphs/memory.png\"><font color=blue>memory</font></a>&nbsp <a target=\"_blank\" href=\"http://autobench.test.netflix.net/$WEB/$dir/graphs/fcache.png\"><font color=blue>fcache</font></a>&nbsp <a target=\"_blank\" href=\"http://autobench.test.netflix.net/$WEB/$dir/graphs/readtput.png\"><font color=blue>rtput</font></a>&nbsp<a target=\"_blank\" href=\"http://autobench.test.netflix.net/$WEB/$dir/graphs/writetput.png\"><font color=blue>wtput</font></a>&nbsp<a target=\"_blank\" href=\"http://autobench.test.netflix.net/$WEB/$dir/graphs/readiops.png\"><font color=blue>riops</font></a>&nbsp <a target=\"_blank\" href=\"http://autobench.test.netflix.net/$WEB/$dir/graphs/writeiops.png\"><font color=blue>wiops</font></a>&nbsp <a target=\"_blank\" href=\"http://autobench.test.netflix.net/$WEB/$dir/graphs/netrxtput.png\"><font color=blue>rxtput</font></a>&nbsp <a target=\"_blank\" href=\"http://autobench.test.netflix.net/$WEB/$dir/graphs/nettxtput.png\"><font color=blue>txtput</font></a>&nbsp <a target=\"_blank\" href=\"http://autobench.test.netflix.net/$WEB/$dir/graphs/netrxpps.png\"><font color=blue>rxpps</font></a>&nbsp <a target=\"_blank\" href=\"http://autobench.test.netflix.net/$WEB/$dir/graphs/nettxpps.png\"><font color=blue>txpps</font></a>");
       }
       if(file_exists("$results/$dir/flamegraph.svg") && ($what == "inst")){
          fwrite($handle, "&nbsp<a target=\"_blank\" href=\"http://autobench.test.netflix.net/$WEB/$dir/flamegraph.svg\"><font color=blue>flamegraph</font></a>");
        }
      fwrite($handle,"\n");
     }
     elseif (file_exists("result.json")){
      fwrite($handle,"NA;");
      fwrite($handle,"NA;");
      fwrite($handle,"NA;");
      //fwrite($handle,"NA;");
      fwrite($handle,"NA;");
      fwrite($handle,"$title;");
      fwrite($handle,"$arguments;");
      fwrite($handle,"$units;");
      fwrite($handle,"$value;");
      fwrite($handle,"NA;"); //system metrics
      fwrite($handle,"NA"); // flame graph
      fwrite($handle,"\n");
      }

      if ($what == "inst"){ 
        fwrite($handlecsv,"$value");
        fwrite($handlecsv,",");
        }
      else {
        $sub = explode('arge',$machine);   // make string with l,xl,2xl,4xl,.. only for graph
        fwrite($handlecsv,"$sub[0]");
        fwrite($handlecsv,";");
        fwrite($handlecsv,"$value");
        fwrite($handlecsv,"\n");
       }
NODATA:
     chdir($results);
  }//foreach $dir
  fclose($handle);
  fclose($handlecsv);
  $i++;  //increment for next text string
  $dirs = "";  //reset dir for next test 
  $data = ""; 
 // At this point we have processed one type of test for a given family, type or inst
 // Let's generate a graph while we are here for this test type
  if ($what == "inst"){
    $csvreport = fopen($csvreport, 'r+') or die('file open failed: ' .$csvreport);
    $stat = fstat($csvreport);
    ftruncate($csvreport, $stat['size']-1);
    fclose($csvreport);

    $data = file_get_contents("$archives/regression-$name/$test/csvreport", true);
    $data = array_map('intval', explode(',', $data));

    $graph = new PHPGraphLib(800,400,"$archives/regression-$name/$test/$test.png");
    $graph->addData($data);
    $graph->setBackgroundColor("white");
    $graph->setTextColor('green');
    $graph->setLegend(true);
    $graph->setLegendTitle('bionic');
    $graph->setLegendTextColor('red');
    $graph->setSwatchOutlineColor('black');
    $graph->setLegendColor('white');
    $graph->setupYAxis(8, 'black');
    $graph->setupXAxis(true);
    $graph->setupXAxis(8, 'black');
    $graph->setTitle("$test");
    $graph->setTitleColor('black');
    $graph->setGrid(false);
    $graph->setGradient('blue', 'silver');
    $graph->setBars(false);
    $graph->setBarColor('red','navy');
    $graph->setBarOutlineColor('white');
    $graph->setLine(true);
    $graph->setLineColor('red');
    $graph->setDataPoints(true);
    $graph->setDataPointColor('green');
    $graph->setDataValues(false);
    $graph->setDataValueColor('red');
   //$graph->setGoalLine(.0025);
   //$graph->setGoalLineColor('red');
    $graph->createGraph();
   }
  else {
    $csvreport = fopen("$archives/compare-$name/$test/csvreport", 'r');
   while(!feof($csvreport)) {
    $temp= fgets($csvreport);
    if (!feof($csvreport)) {  //to avoid reading extra line
    $temp= rtrim($temp);
    $array = explode(';',$temp);
    $key = $array[0];
    $value = $array[1];
    $data[$key] = (int)$value;
     }
    }
    fclose($csvreport);
    $data = array_filter($data); // remove empty elements in array

    $graph = new PHPGraphLib(800,400,"$archives/compare-$name/$test/$test.png");
    $graph->addData($data);
    $graph->setBackgroundColor("white");
    $graph->setTextColor('green');
    $graph->setLegend(true);
    $graph->setLegendTitle('bionic');
    $graph->setLegendTextColor('red');
    $graph->setSwatchOutlineColor('black');
    $graph->setLegendColor('white');
    $graph->setupYAxis(8, 'black');
    $graph->setupXAxis(true);
    $graph->setupXAxis(8, 'black');
    $graph->setTitle("$test");
    $graph->setTitleColor('black');
    $graph->setGrid(false);
    $graph->setGradient('blue', 'silver');
    $graph->setBars(true);
       //$graph->setBarColor('green');
    $graph->setBarOutlineColor('white');
    $graph->setLine(false);
    $graph->setLineColor('red');
    $graph->setDataPoints(true);
    $graph->setDataPointColor('red');
    $graph->setDataValues(true);
    $graph->setDataValueColor('green');
    $graph->setXValuesHorizontal(true);
      //$graph->setGoalLine(.0025);
      //$graph->setGoalLineColor('red');
    $graph->createGraph();
  }
EMPTYDIR:
 }//tests
}//family, types, inst
}// functio
?>
