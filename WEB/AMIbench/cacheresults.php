<?php

include("phpgraphlib/phpgraphlib.php");

$phoronix="/efs/amibench/phoronix-test-suite/pts-core/phoronix-test-suite.php";
$ini_array = parse_ini_file("config.ini");
$results=$ini_array["results"];
chdir($results);
$archives=($ini_array["archives"]);
$error="At least two saved result names must be supplied.";
//clean up
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
//compareFamilies
if (!empty($families)){
 foreach ($families as $family){
  if (!empty($cputests)){
   foreach ($cputests as $test){
     $output=0;
     exec("sudo php $phoronix merge-results cputests-$test-$family-*-LATEST", $output);
     if(in_array($error,$output)){
       print "At least two saved result must be specified for test:$test\n";
      }
     else{
       exec("sudo mkdir -p  $archives/compare-$family/$test");
       exec("sudo chmod -R 777 $archives/compare-$family/$test");
       exec("sudo cp -r merge-*/result-graphs $archives/compare-$family/$test");
       exec("sudo rm -rf merge-*");
     }
    }
   }
  if (!empty($memtests)){
   foreach ($memtests as $test){
     $output=0;
     exec("sudo php $phoronix merge-results memtests-$test-$family-*-LATEST", $output);
     if(in_array($error,$output)){
       print "Atleast two saved result must be specified for test:$test\n";
      }
     else{
     exec("sudo mkdir -p  $archives/compare-$family/$test");
     exec("sudo chmod -R 777 $archives/compare-$family/$test");
     exec("sudo cp -r merge-*/result-graphs $archives/compare-$family/$test");
     exec("sudo rm -rf merge-*");
     }
    }
  }
  if (!empty($javatests)){
   foreach ($javatests as $test){
     $output=0;
     exec("sudo php $phoronix merge-results javatests-$test-$family-*-LATEST", $output);
     if(in_array($error,$output)){
       print "Atleast two saved results must be specified for test:$test\n";
      }
     else{
     exec("sudo mkdir -p  $archives/compare-$family/$test");
     exec("sudo chmod -R 777 $archives/compare-$family/$test");
     exec("sudo cp -r merge-*/result-graphs $archives/compare-$family/$test");
     exec("sudo rm -rf merge-*");
     }
   }
  }
  if (!empty($iotests)){
   foreach ($iotests as $test){
     $output=0;
     exec("sudo php $phoronix merge-results iotests-$test-$family-*-LATEST",$output);
     if(in_array($error,$output)){
       print "Atleast two saved results must be specified for test:$test\n";
      }
     else{
     exec("sudo mkdir -p  $archives/compare-$family/$test");
     exec("sudo chmod -R 777 $archives/compare-$family/$test");
     exec("sudo cp -r merge-*/result-graphs $archives/compare-$family/$test");
     exec("sudo rm -rf merge-*");
    }
   }
  }
 }
}
//compareTypes
if (!empty($types)){
 foreach ($types as $type){
  if (!empty($cputests)){
   foreach ($cputests as $test){
      $output=0;
      exec("sudo php $phoronix merge-results cputests-$test-*-$type-LATEST",$output);
      if (in_array($error,$output)){
       print "Atleast two saved results must be specified for test:$test\n";
      } 
      else{
         exec("sudo mkdir -p $archives/compare-$type/$test");
         exec("sudo chmod -R 777 $archives/compare-$type/$test");
         exec("sudo cp -r merge-*/result-graphs $archives/compare-$type/$test");
         exec("sudo rm $archives/compare-$type/$test/result-graphs/*_table.svg");
         exec("sudo rm -rf merge-*");
     }
    }
   }
   if (!empty($memtests)){
    foreach ($memtests as $test){
      $output=0;
      exec("sudo php $phoronix merge-results memtests-$test-*-$type-LATEST",$output);
      if (in_array($error,$output)){
       print "Atleast two saved results must be specified for test:$test\n";
      }
      else{
      exec("sudo mkdir -p $archives/compare-$type/$test");
      exec("sudo chmod -R 777 $archives/compare-$type/$test");
      exec("sudo cp -r merge-*/result-graphs $archives/compare-$type/$test");
      exec("sudo rm $archives/compare-$type/$test/result-graphs/*_table.svg");
      exec("sudo rm -rf merge-*");
     }
    }
   }
   if (!empty($javatests)){
    foreach ($javatests as $test){
      $output=0;
      exec("sudo php $phoronix merge-results javatests-$test-*-$type-LATEST",$output);
      if (in_array($error,$output)){
       print "Atleast two saved result must be specified for test:$test\n";
      }
      else{
      exec("sudo mkdir -p  $archives/compare-$type/$test");
      exec("sudo chmod -R 777 $archives/compare-$type/$test");
      exec("sudo cp -r merge-*/result-graphs $archives/compare-$type/$test");
      exec("sudo rm -rf merge-*");
     }
    }
   }
   if (!empty($iotests)){
    foreach ($iotests as $test){
      $output=0;
      exec("sudo php $phoronix merge-results iotests-$test-*-$type-LATEST",$output);
      if (in_array($error,$output)){
       print "Atleast two saved results must be specified for test:$test\n";
      }
      else{
      exec("sudo mkdir -p  $archives/compare-$type/$test");
      exec("sudo chmod -R 777 $archives/compare-$type/$test");
      exec("sudo cp -r merge-*/result-graphs $archives/compare-$type/$test");
      exec("sudo rm -rf merge-*");
     }
   }
  }
 }
}
//regression 
if (!empty($inst)){
 foreach ($inst as $ins){
  if (!empty($cputests)){
   foreach ($cputests as $test){
      $output=0;
      exec("sudo php $phoronix merge-results cputests-$test-$ins-*-*",$output);
      if (in_array($error,$output)){
       print "Atleast two saved results must be specified for test:$test\n";
      }
      else{
      exec("sudo mkdir -p $archives/regression-$ins/$test");
      exec("sudo chmod -R 777 $archives/regression-$ins/$test");
      exec("sudo cp -r merge-*/result-graphs $archives/regression-$ins/$test");
      exec("sudo rm $archives/regression-$ins/$test/result-graphs/*_table.svg");
      exec("sudo rm -rf merge-*");
     }
    }
   }
  if (!empty($memtests)){
   foreach ($memtests as $test){
      $output=0;
      exec("sudo php $phoronix merge-results memtests-$test-$ins-*-*",$output);
      if (in_array($error,$output)){
       print "Atleast two saved results must be specified for test:$test\n";
      }
      else{
      exec("sudo mkdir -p $archives/regression-$ins/$test");
      exec("sudo chmod -R 777 $archives/regression-$ins/$test");
      exec("sudo cp -r merge-*/result-graphs $archives/regression-$ins/$test");
      exec("sudo rm $archives/regression-$ins/$test/result-graphs/*_table.svg");
      exec("sudo rm -rf merge-*");
     }
    }
   }
  if (!empty($javatests)){
   foreach ($javatests as $test){
      $output=0;
      exec("sudo php $phoronix merge-results javatests-$test-$ins-*-*",$output);
      if (in_array($error,$output)){
       print "Atleast two saved results must be specified for test:$test\n";
      }
      else{
      exec("sudo mkdir -p  $archives/regression-$ins/$test");
      exec("sudo chmod -R 777 $archives/regression-$ins/$test");
      exec("sudo cp -r merge-*/result-graphs $archives/regression-$ins/$test");
      exec("sudo rm $archives/regression-$ins/$test/result-graphs/*_table.svg");
      exec("sudo rm -rf merge-*");
     }
    }
   }
  if (!empty($iotests)){
   foreach ($iotests as $test){
      $output=0;
      exec("sudo php $phoronix merge-results iotests-$test-$ins-*-*",$output);
      if (in_array($error,$output)){
       print "Atleast two saved results must be specified for test:$test\n";
      }
      else{
      exec("sudo mkdir -p  $archives/regression-$ins/$test");
      exec("sudo chmod -R 777 $archives/regression-$ins/$test");
      exec("sudo cp -r merge-*/result-graphs $archives/regression-$ins/$test");
      exec("sudo rm $archives/regression-$ins/$test/result-graphs/*_table.svg");
      exec("sudo rm -rf merge-*");
     }
    }
   }
 }
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
     processjson($types, "type","ioteststs");
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
}

function processjson($names, $what, $testtype){
 global $phoronix;
 global $archives;
 global $results;
 global $families;
 global $cputests, $memtests, $javatests, $iotests;
 global $cputeststrings, $memteststrings, $javateststrings, $ioteststrings;
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
 
 foreach ($names as $name){
   $i=0;  //teststrings array
   foreach ($mytests as $test){
    if ($what == "inst") {
      if (!is_dir("$archives/regression-$name/$test")){
       print "test:$test Failed! Directory:$archives/regression-$name/$test is missing..skipping \n";
       continue;
      }
      else {
         $testreport = "$archives/regression-$name/$test/testreport";
         $testcsvxenial  = "$archives/regression-$name/$test/testcsvxenial";

         $handle = fopen($testreport, 'a') or die('file open failed: ' .$testreport);
         $handlecsvxenial = fopen($testcsvxenial, 'a') or die('file open failed: ' .$testcsvxenial);

         exec("ls -dtr $testtype-$test-$name-*-*", $dirs);  
       }
    }
    elseif (($what == "family") || ($what == "type")){ 
      if (!is_dir("$archives/compare-$name/$test")){
       print "test:$test Failed! Directory:$archives/compare-$name/$test is missing..skipping \n";
       continue;
      }
      else {
        $testreport = "$archives/compare-$name/$test/testreport";
        $testcsvxenial  = "$archives/compare-$name/$test/testcsvxenial";

        $handle = fopen($testreport, 'a') or die('file open failed: ' .$testreport);
        $handlecsvxenial = fopen($testcsvxenial, 'a') or die('file open failed: ' .$testcsvxenial);

       if ($what == "family")  exec("ls -d $testtype-$test-$name-*-LATEST", $dirs);
       if ($what == "type")    exec("ls -d $testtype-$test-*-$name-LATEST", $dirs); 
     }
    }
  foreach ($dirs as $dir){
     chdir($dir);
     if (file_exists("result.json")){
      $string = file_get_contents("result.json");
      $data = json_decode($string);
      $teststring = $myteststrings[$i];
      chdir("system-logs");
      exec("ls -d *", $versions);
      foreach ($versions as $ver){
      $machine = $ver;
      }
      chdir($results);
      $pattern = explode('/',$teststring);
      if (preg_match("/$pattern[1]/",$string,$matches)){
        $arguments = $data->{'results'}->{$teststring}->arguments;
        $units = $data->{'results'}->{$teststring}->units;
        $value = $data->{'results'}->{$teststring}->{'results'}->{$machine}->value;

        fwrite($handle,"$test;");
        fwrite($handle,"$machine;");
        fwrite($handle,"$arguments;");
        fwrite($handle,"$units;");
        fwrite($handle,"$value");
        fwrite($handle,"\n");

        if ($what == "inst"){
         fwrite($handlecsvxenial,"$value");
         fwrite($handlecsvxenial,",");
	}
        else{
         $sub = explode('arge',$machine);   // make string with l,xl,2xl,4xl,.. only for graph
         fwrite($handlecsvxenial,"$sub[0]");
         fwrite($handlecsvxenial,";");
         fwrite($handlecsvxenial,"$value");
         fwrite($handlecsvxenial,"\n");
        }
       }
    }//file_exits
    else {
        chdir($results);
    }
   }//foreach

    fclose($handle);
    fclose($handlecsvxenial);
    $i++;    //increment for next test string
    $dirs = "";  //reset dir for next test 
//------------------------//
    $mydata = ""; 

   if ($what == "inst"){
    $testcsvxenial = fopen($testcsvxenial, 'r+') or die('file open failed: ' .$testcsvxenial);
    $stat = fstat($testcsvxenial);
    ftruncate($testcsvxenial, $stat['size']-1);
    fclose($testcsvxenial);

    $mydata = file_get_contents("$archives/regression-$name/$test/testcsvxenial", true);
    $mydata = array_map('intval', explode(',', $mydata));

    $graph = new PHPGraphLib(600,400,"$archives/regression-$name/$test/$test.png");
    $graph->addData($mydata);
    $graph->setBackgroundColor("white");
    $graph->setTextColor('navy');
    $graph->setLegend(true);
    //$graph->setLegendTitle('xenial');
    $graph->setLegendTitle('xenial');
    $graph->setLegendTextColor('red');
    $graph->setSwatchOutlineColor('white');
    $graph->setLegendColor('white');
    $graph->setupYAxis(8, 'navy');
    $graph->setupXAxis(true);
    $graph->setupXAxis(8, 'navy');
    $graph->setTitle("$test");
    $graph->setTitleColor('red');
    $graph->setGrid(false);
   //$graph->setGradient('white', 'silver');
    $graph->setBars(false);
    $graph->setBarColor('green');
    $graph->setBarOutlineColor('white');
    $graph->setLine(true);
    $graph->setLineColor('navy');
    $graph->setDataPoints(true);
    $graph->setDataPointColor('red');
    $graph->setDataValues(false);
    $graph->setDataValueColor('navy');
   //$graph->setGoalLine(.0025);
   //$graph->setGoalLineColor('red');
    $graph->createGraph();
   }

  else {
    $file = fopen("$archives/compare-$name/$test/testcsvxenial", 'r');
   while(!feof($file)) {
    $temp= fgets($file);
    if (!feof($file)) {  //to avoid reading extra line
    $temp= rtrim($temp);
    $array = explode(';',$temp);
    $key = $array[0];
    $value = $array[1];
    $mydata[$key] = (int)$value;
     }
    }
    $mydata = array_filter($mydata); // remove empty elements in array
    fclose($file);

    $graph = new PHPGraphLib(600,400,"$archives/compare-$name/$test/$test.png");
    $graph->addData($mydata);
    $graph->setBackgroundColor("white");
    $graph->setTextColor('navy');
    $graph->setLegend(true);
    $graph->setLegendTitle('xenial');
    $graph->setLegendTextColor('red');
    $graph->setSwatchOutlineColor('white');
    $graph->setLegendColor('white');
    $graph->setupYAxis(8, 'navy');
    $graph->setupXAxis(true);
    $graph->setupXAxis(8, 'navy');
    $graph->setTitle("$test");
    $graph->setTitleColor('red');
    $graph->setGrid(false);
    $graph->setGradient('white', 'silver');
    $graph->setBars(true);
       //$graph->setBarColor('green');
    $graph->setBarOutlineColor('white');
    $graph->setLine(false);
    $graph->setLineColor('navy');
    $graph->setDataPoints(true);
    $graph->setDataPointColor('red');
    $graph->setDataValues(true);
    $graph->setDataValueColor('navy');
    $graph->setXValuesHorizontal(true);
      //$graph->setGoalLine(.0025);
      //$graph->setGoalLineColor('red');
    $graph->createGraph();
   }
  }
 }
}

?>
