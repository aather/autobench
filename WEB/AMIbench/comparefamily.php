<?php include 'menu.php'; ?>
<?php
$ini_array = parse_ini_file("config.ini");
$results=$ini_array["results"];
chdir($results);
$archives=$ini_array["archives"];
$Archives=$ini_array["reporturl"];

print <<<HEAD
<style>
a:active {
    background-color: yellow;
}
a {
    color: white;
}
a:hover {
    background-color: white;
}
</style>
HEAD;

$found=$_POST["family"]; //r5-mem, r5-cpu...
if (strpos($found, "mem")){
   $family= chop ($found,"mem"); 
   $family= chop ($family,"-"); 
}
else if (strpos($found, "jvm")){
   $family= chop ($found,"jvm"); 
   $family= chop ($family,"-"); 
}
else if (strpos($found, "io")){
   $family= chop ($found,"io"); 
   $family= chop ($family,"-"); 
}
else if (strpos($found, "s3")){
   $family= chop ($found,"s3"); 
   $family= chop ($family,"-"); 
}
else {
   $family= chop ($found,"cpu");
   $family= chop ($family,"-");
 }
chdir("/efs/html/AMIbench");
try
{
    //open the database
    $db = new PDO('sqlite:AMIbench.sqlite');

    //now output the data to a simple html table...
print <<<HEAD
<div class="container-fluid">
  <div class="row row-content">
     <div class="col-xs-12">
      <div class="table-responsive">
       <table class="table table-striped table-hover">
        <tr class=warn>
        <th class=warn>inst</th>
        <th>model</th>
        <th>vcpu</th>
        <th>memory(GB)</th>
        <th>storage(GB)</th>
        <th><a href="https://confluence.netflix.com/display/CLDPERF/AWS+Instances+Network+Throughput" target="_blank">network(Mbps)</a></th>
        <th><a href="https://aws.amazon.com/about-aws/whats-new/2016/06/introducing-elastic-network-adapter-ena-the-next-generation-network-interface-for-ec2-instances/" target="_blank">ena</a></th>
        <th><a href="https://confluence.netflix.com/display/CLDPERF/AWS+I2+and+I3+Instant+Types+Comparison" target="_blank">nvme</a></td>
        <th><a href="https://aws.amazon.com/ec2/faqs/#enhanced-networking" target="_blank">sriov</a></th>
        <th><a href="http://techblog.cloudperf.net/2016/05/2-million-packets-per-second-on-public.html" target="_blank">xennet</a></th>
        <th><a href="http://docs.aws.amazon.com/AWSEC2/latest/UserGuide/EBSOptimized.html" target="_blank">ebsopt(tput:iops)</a></th>
        <th><a href="http://techblog.cloudperf.net/2016/09/exploring-numa-on-amazon-cloud-instances.html" target="_blank">numa</a></th>
        <th class=warn> price</th>
        </tr>
HEAD;

    $stmt = $db->prepare("SELECT * FROM Instype WHERE name LIKE ?");
    $stmt->execute(["%$family%"]);
    while($row = $stmt->fetch())
    {
         print "<tr>";
         print " <th class=warn>".$row['name']. "</th>";
         print "<td> ".$row['model']. "</td>";
         print "<td> ".$row['vcpus']. "</td> " ;
         print " <td> ".$row['memory']. "</td> ";
         if ($row['storage'] == 0){
           print " <td> N </td> ";
          }
         else{ print " <td> ".$row['storage']. "</td>";}
         print " <td> ".$row['network']. "</td>";
         if ($row['eni'] == 0){
           print " <td> N </td> ";
          }
         else{ print " <td> Y </td> ";}
         if ($row['nvme'] == 0){
           print " <td> N </td> ";
          }
         else{ print " <td> Y </td> ";}
         if ($row['sriov'] == 0){
           print " <td> N </td> ";
          }
         else{ print " <td> Y </td> ";}
         if ($row['xennet'] == 0){
           print " <td> N </td> ";
          }
         else{ print " <td> Y </td> ";}
         if ($row['ebsopt'] == '0'){
           print " <td> N </td> ";
          }
         else{ print " <td>" . $row['ebsopt'] . " </td> ";}
         if ($row['numa'] == 0){
           print " <td> N </td> ";
          }
         else{ print " <td> Y </td> ";}
	 if ($row['price'] == 0){
           print " <td> NA </td> ";
          }
         else{ print " <td>$ ".$row['price']. "</td>";}
         #print " <td>$ ".$row['price']. "</td>";
        print " </tr>";
    }
      print "</table>";
  print " </div>";
 print "</div>";
print "</div>";
print "</div>";
    // close the database connection
    $db = NULL;
}
  catch(PDOException $e)
  {
    print 'Exception : '.$e->getMessage();
  }
// End of table 
print <<<HEAD
<style>
a:active {
    background-color: yellow;
}
a {
    color: white;
}

.zoom {
-webkit-transition: all 0.35s ease-in-out;
-moz-transition: all 0.35s ease-in-out;
transition: all 0.35s ease-in-out;
cursor: -webkit-zoom-in;
cursor: -moz-zoom-in;
cursor: zoom-in;
}

.zoom:hover,
.zoom:active,
.zoom:focus {
-ms-transform: scale(1.5);
-moz-transform: scale(1.5);
-webkit-transform: scale(1.5);
-o-transform: scale(1.5);
transform: scale(1.5);
position:relative;
z-index:100;
}
</style>
HEAD;

if (strpos($found, "cpu")){
 $cputests=$ini_array["cputestname"];
 foreach ($cputests as $test){
print <<<HEAD
<div class="container-fluid">
  <div class="row row-content">
    <div class="col-xs-2">
     <div class="table-responsive">
       <table class="table table-striped table-hover">
	<tr class="info">
        <td><b><font color="red">CPU Tests</font></b></td>
        </tr>
        <tr class=warn>
HEAD;
      foreach ($cputests as $mytest){
        print "<tr class=warn>";
        print "<th><a href=\"#$mytest\">$mytest</a></th></tr>";
      }

    print <<<HEAD
</table>
</div>
</div>
<div class="col-xs-10">
 <a name=$test></a>
 <b><font color=gray>$test</font></b>
  <div class="table-responsive">
   <table class="table table-striped table-hover">
     <tr class=warn>
      <th>Date</th>
      <th>BaseAMI</th>
      <th>Unit</th>
      <th>Value</th>
      <th>Test | Instance Type | Kernel Version</th>
     </tr>
HEAD;
 $sorted = "$archives/compare-$family/$test/testreportsorted";
 $testreport = "$archives/compare-$family/$test/report";
 if (file_exists("$sorted")){
  if (filesize("$sorted") == 0){
    unlink("$sorted");
    if (($test == "ffmpeg-250")||($test == "encode-mp3-150")|| ($test == "sysbench-cpu-100")){ 
      exec("sort -k8 -t\";\" -n $testreport > $sorted");
      $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
     }
    else{
       exec("sort -k8 -t\";\" -nr $testreport > $sorted");
       $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
     }
    }
  else {
   $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
   }
 }
 else {
   if (($test == "ffmpeg-250")||($test == "encode-mp3-150")|| ($test == "sysbench-cpu-100")){
      exec("sort -k8 -t\";\" -n $testreport > $sorted");
      $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
     }
    else{
       exec("sort -k8 -t\";\" -nr $testreport > $sorted");
       $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
     }
 }
  $i=0;
  while(!feof($handle)) {
    $line = fgets($handle);
    $pattern = explode(';',$line); 
    if ($i == 0){
      print <<<HEAD
        <tr class=warn>
        <td><b>$pattern[0]</td>
        <td>$pattern[1]</td>
        <td>$pattern[6]</td>
        <td class=info><b><font color=red>$pattern[7]*</td>
        <td>$pattern[4]</td>
        </tr>
HEAD;
    $i=1;
    }
    else{
	print <<<HEAD
        <tr class=warn>
        <td><b>$pattern[0]</td>
        <td>$pattern[1]</td>
        <td>$pattern[6]</td>
        <td class=info><b><font color=blue>$pattern[7]</td>
        <td>$pattern[4]</td>
        </tr>
HEAD;
   }
 }
 fclose($handle);
print <<<HEAD
</table>
</div>
<div class="col-xs-10">
<table class="table table-condensed table-hover">
<tr class=warning><td><font color=red>System Metrics:</font></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/cpu.png"><font color=blue>cpu </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/memory.png"><font color=blue>memory</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/fcache.png"><font color=blue>fscache</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readtput.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writetput.png"><font color=blue>wtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readiops.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writeiops.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxtput.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxtput.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxpps.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxpps.png"><font color=blue>tx pps</font></a></td>
</tr></table>
</div>
HEAD;
if (file_exists("$archives/compare-$family/$test/graphs/cpu-1.png")){
print <<<HEAD
<div class="col-xs-10">
<table class="table table-condensed table-hover">
<tr class=warning><td><font color=red>System Metrics:</font></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/cpu-1.png"><font color=blue>cpu </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/memory-1.png"><font color=blue>memory</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/fcache-1.png"><font color=blue>fscache</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readtput-1.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writetput-1.png"><font color=blue>wtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readiops-1.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writeiops-1.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxtput-1.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxtput-1.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxpps-1.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxpps-1.png"><font color=blue>txpps</font></a></td>
</tr></table>
</div>
HEAD;
}
print <<<HEAD
<div class="col-xs-10">
<font color=red><small><i>* graphic package supports maximum of 5 data sources </i></small></font>
</div>
 <a name="$test"></a> <img src="$Archives/compare-$family/$test/$test.png" class="img-responsive" >
  </div>
 </div>
</div>
HEAD;
}
}//if
else if (strpos($found, "mem")){
 $memtests=$ini_array["memtestname"];
 foreach ($memtests as $test){
    print <<<HEAD
 <div class="container-fluid">
  <div class="row row-content">
    <div class="col-xs-2">
     <div class="table-responsive">
       <table class="table table-striped table-hover">
        <tr class="info">
        <td><b><font color="red">Memory Tests</font></b></td>
        </tr>
        <tr class=warn>

HEAD;
      foreach ($memtests as $mytest){
        print "<tr class=warn>";
        print "<th><a href=\"#$mytest\">$mytest</a></th></tr>";
      }

    print <<<HEAD
</table>
</div>
</div>
<div class="col-xs-10">
 <a name=$test></a>
 <b><font color=gray>$test</font></b>
  <div class="table-responsive">
   <table class="table table-striped table-hover">
   <tr class=warn>
      <th>Date</th>
      <th>BaseAMI</th>
      <th>Unit</th>
      <th>Value</th>
      <th>Test | Instance Type | Kernel Version</th>
     </tr>
HEAD;
$sorted = "$archives/compare-$family/$test/testreportsorted";
 $testreport = "$archives/compare-$family/$test/report";
 if (file_exists("$sorted")){
  if (filesize("$sorted") == 0){
    unlink("$sorted");
    if ($test == "lmbench-mem-100"){
      exec("sort -k8 -t\";\" -n $testreport > $sorted");
      $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
     }
    else{
       exec("sort -k8 -t\";\" -nr $testreport > $sorted");
       $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
     }
    }
  else {
   $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
   }
 }
 else {
     if ($test == "lmbench-mem-100"){
      exec("sort -k8 -t\";\" -n $testreport > $sorted");
      $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
     }
    else{
       exec("sort -k8 -t\";\" -nr $testreport > $sorted");
       $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
     }
 }
  $i=0;
  while(!feof($handle)) {
    $line = fgets($handle);
    $pattern = explode(';',$line);
    if ($i == 0){
      print <<<HEAD
      <tr class=warn>
        <td><b>$pattern[0]</td>
        <td>$pattern[1]</td>
        <td>$pattern[6]</td>
        <td class=info><b><font color=red>$pattern[7]*</td>
        <td>$pattern[4]</td>
        </tr>
HEAD;
    $i=1;
    }
    else{
        print <<<HEAD
      <tr class=warn>
        <td><b>$pattern[0]</td>
        <td>$pattern[1]</td>
        <td>$pattern[6]</td>
        <td class=info><b><font color=blue>$pattern[7]</td>
        <td>$pattern[4]</td>
        </tr>
HEAD;
   }
  }
  fclose($handle);
print <<<HEAD
</table>
</div>
<div class="col-xs-10">
<table class="table table-condensed table-hover">
<tr class=warning><td><font color=red>System Metrics:</font></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/cpu.png"><font color=blue>cpu </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/memory.png"><font color=blue>memory</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/fcache.png"><font color=blue>fscache</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readtput.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writetput.png"><font color=blue>wtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readiops.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writeiops.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxtput.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxtput.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxpps.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxpps.png"><font color=blue>txpps</font></a></td>
</tr></table>
</div>
HEAD;
if (file_exists("$archives/compare-$family/$test/graphs/cpu-1.png")){
print <<<HEAD
<div class="col-xs-10">
<table class="table table-condensed table-hover">
<tr class=warning><td><font color=red>System Metrics:</font></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/cpu-1.png"><font color=blue>cpu</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/memory-1.png"><font color=blue>memory</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/fcache-1.png"><font color=blue>fscache</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readtput-1.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writetput-1.png"><font color=blue>wtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readiops-1.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writeiops-1.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxtput-1.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxtput-1.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxpps-1.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxpps-1.png"><font color=blue>txpps</font></a></td>
</tr></table>
</div>
HEAD;
}
print <<<HEAD
<div class="col-xs-10">
<font color=red><small><i>* graphic package supports maximum of 5 data sources </i></small></font>
</div>
 <a name="$test"></a> <img src="$Archives/compare-$family/$test/$test.png" class="img-responsive" >
  </div>
 </div>
</div>
HEAD;
 }//foreach
}//if
else if (strpos($found, "jvm")){
 $javatests=$ini_array["javatestname"];
 foreach ($javatests as $test){
   print <<<HEAD
  <div class="container-fluid">
   <div class="row row-content">
    <div class="col-xs-2">
     <div class="table-responsive">
       <table class="table table-striped table-hover">
      <tr class="info">
        <td><b><font color="red">Java Tests</font></b></td>
        </tr>
        <tr class=warn>
HEAD;
      foreach ($javatests as $mytest){
        print "<tr class=warn>";
        print "<th><a href=\"#$mytest\">$mytest</a></th></tr>";
      }

    print <<<HEAD
</table>
</div>
</div>
<div class="col-xs-10">
 <a name=$test></a>
 <b><font color=gray>$test</font></b>
  <div class="table-responsive">
   <table class="table table-striped table-hover">
  <tr class=warn>
      <th>Date</th>
      <th>JVM</th>
      <th>Unit</th>
      <th>Value</th>
      <th>Test | Instance Type | Kernel Version</th>
     </tr>
HEAD;
$sorted = "$archives/compare-$family/$test/testreportsorted";
 $testreport = "$archives/compare-$family/$test/report";
 if (file_exists("$sorted")){
  if (filesize("$sorted") == 0){
    unlink("$sorted");
    exec("sort -k8 -t\";\" -nr $testreport > $sorted");
    $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
     }
  else {
   $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
   }
 }
 else {
      exec("sort -k8 -t\";\" -nr $testreport > $sorted");
      $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
 }
  $i=0;
  while(!feof($handle)) {
    $line = fgets($handle);
    $pattern = explode(';',$line);
    if ($i == 0){
      print <<<HEAD
    <tr class=warn>
        <td><b>$pattern[0]</td>
        <td>$pattern[3]</td>
        <td>$pattern[6]</td>
        <td class=info><b><font color=red>$pattern[7]*</td>
        <td>$pattern[4]</td>
        </tr>
HEAD;
    $i=1;
    }
    else{
        print <<<HEAD
	<tr class=warn>
        <td><b>$pattern[0]</td>
        <td>$pattern[3]</td>
        <td>$pattern[6]</td>
        <td class=info><b><font color=blue>$pattern[7]</td>
        <td>$pattern[4]</td>
        </tr>
HEAD;
   }
 }
 fclose($handle);
print <<<HEAD
</table>
</div>
<div class="col-xs-10">
<table class="table table-condensed table-hover">
<tr class=warning><td><font color=red>Resource Usage:</font></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/cpu.png"><font color=blue>cpu </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/memory.png"><font color=blue>memory</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/fcache.png"><font color=blue>fscache </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readtput.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writetput.png"><font color=blue>wtput </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readiops.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writeiops.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxtput.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxtput.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxpps.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxpps.png"><font color=blue>txpps</font></a></td>
</tr></table>
</div>
HEAD;
if (file_exists("$archives/compare-$family/$test/graphs/cpu-1.png")){
print <<<HEAD
<div class="col-xs-10">
<table class="table table-condensed table-hover">
<tr class=warning><td><font color=red>Resource Usage(6-9):</font></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/cpu-1.png"><font color=blue>cpu </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/memory-1.png"><font color=blue>memory </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/fcache-1.png"><font color=blue>fscache </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readtput-1.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writetput-1.png"><font color=blue>wtput </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readiops-1.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writeiops-1.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxtput-1.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxtput-1.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxpps-1.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxpps-1.png"><font color=blue>txpps</font></a></td>
</tr></table>
</div>
HEAD;
}
print <<<HEAD
<div class="col-xs-10">
<font color=red><small><i>* graphic package supports maximum of 5 data sources </i></small></font>
</div>
 <a name="$test"></a> <img src="$Archives/compare-$family/$test/$test.png" class="img-responsive" >
  </div>
 </div>
</div>
HEAD;
 }//foreach
}
else if (strpos($found,"io")){
 $iotests=$ini_array["iotestname"];
 foreach ($iotests as $test){
   print <<<HEAD
  <div class="container-fluid">
   <div class="row row-content">
    <div class="col-xs-2">
     <div class="table-responsive">
       <table class="table table-striped table-hover">
      <tr class="info">
        <td><b><font color="red">Java Tests</font></b></td>
        </tr>
        <tr class=warn>
HEAD;
      foreach ($iotests as $mytest){
        print "<tr class=warn>";
        print "<th><a href=\"#$mytest\">$mytest</a></th></tr>";
      }

    print <<<HEAD
</table>
</div>
</div>
<div class="col-xs-10">
 <a name=$test></a>
 <b><font color=gray>$test</font></b>
  <div class="table-responsive">
   <table class="table table-striped table-hover">
  <tr class=warn>
      <th>Date</th>
      <th>JVM</th>
      <th>Unit</th>
      <th>Value</th>
      <th>Test | Instance Type | Kernel Version</th>
     </tr>
HEAD;
$sorted = "$archives/compare-$family/$test/testreportsorted";
 $testreport = "$archives/compare-$family/$test/report";
 if (file_exists("$sorted")){
  if (filesize("$sorted") == 0){
    unlink("$sorted");
    exec("sort -k8 -t\";\" -nr $testreport > $sorted");
    $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
     }
  else {
   $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
   }
 }
 else {
      exec("sort -k8 -t\";\" -nr $testreport > $sorted");
      $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
 }
  $i=0;
  while(!feof($handle)) {
    $line = fgets($handle);
    $pattern = explode(';',$line);
    if ($i == 0){
      print <<<HEAD
      <tr class=warn>
        <td><b>$pattern[0]</td>
        <td>$pattern[3]</td>
        <td>$pattern[6]</td>
        <td class=info><b><font color=red>$pattern[7]*</td>
        <td>$pattern[4]</td>
        </tr>
HEAD;
    $i=1;
    }
    else{
        print <<<HEAD
        <tr class=warn>
        <td><b>$pattern[0]</td>
        <td>$pattern[3]</td>
        <td>$pattern[6]</td>
        <td class=info><b><font color=blue>$pattern[7]</td>
        <td>$pattern[4]</td>
        </tr>
HEAD;
   }
 }
 fclose($handle);
print <<<HEAD
</table>
</div>
<div class="col-xs-10">
<table class="table table-condensed table-hover">
<tr class=warning><td><font color=red>Resource Usage:</font></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/cpu.png"><font color=blue>cpu </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/memory.png"><font color=blue>memory</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/fcache.png"><font color=blue>fscache </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readtput.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writetput.png"><font color=blue>wtput </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readiops.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writeiops.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxtput.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxtput.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxpps.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxpps.png"><font color=blue>txpps</font></a></td>
</tr></table>
</div>
HEAD;
if (file_exists("$archives/compare-$family/$test/graphs/cpu-1.png")){
print <<<HEAD
<div class="col-xs-10">
<table class="table table-condensed table-hover">
<tr class=warning><td><font color=red>Resource Usage(6-9):</font></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/cpu-1.png"><font color=blue>cpu </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/memory-1.png"><font color=blue>memory </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/fcache-1.png"><font color=blue>fscache </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readtput-1.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writetput-1.png"><font color=blue>wtput </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readiops-1.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writeiops-1.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxtput-1.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxtput-1.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxpps-1.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxpps-1.png"><font color=blue>txpps</font></a></td>
</tr></table>
</div>
HEAD;
}
print <<<HEAD
<div class="col-xs-10">
<font color=red><small><i>* graphic package supports maximum of 5 data sources </i></small></font>
</div>
 <a name="$test"></a> <img src="$Archives/compare-$family/$test/$test.png" class="img-responsive" >
  </div>
 </div>
</div>
HEAD;
 }//found
}
else if (strpos($found, "s3")){
 $s3tests=$ini_array["s3testname"];
 foreach ($s3tests as $test){
   print <<<HEAD
  <div class="container-fluid">
   <div class="row row-content">
    <div class="col-xs-2">
     <div class="table-responsive">
       <table class="table table-striped table-hover">
      <tr class="info">
        <td><b><font color="red">S3 Tests</font></b></td>
        </tr>
        <tr class=warn>
HEAD;
      foreach ($s3tests as $mytest){
        print "<tr class=warn>";
        print "<th><a href=\"#$mytest\">$mytest</a></th></tr>";
      }

    print <<<HEAD
</table>
</div>
</div>
<div class="col-xs-10">
 <a name=$test></a>
 <b><font color=gray>$test</font></b>
  <div class="table-responsive">
   <table class="table table-striped table-hover">
  <tr class=warn>
      <th>Date</th>
      <th>BaseAMI</th>
      <th>Unit</th>
      <th>Value</th>
      <th>Test | Instance Type | Kernel Version</th>
     </tr>
HEAD;
$sorted = "$archives/compare-$family/$test/testreportsorted";
 $testreport = "$archives/compare-$family/$test/report";
 if (file_exists("$sorted")){
  if (filesize("$sorted") == 0){
    unlink("$sorted");
    exec("sort -k8 -t\";\" -nr $testreport > $sorted");
    $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
     }
  else {
   $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
   }
 }
 else {
      exec("sort -k8 -t\";\" -nr $testreport > $sorted");
      $handle = fopen($sorted, 'r') or die('file open failed: ' .$sorted);
 }
  $i=0;
  while(!feof($handle)) {
    $line = fgets($handle);
    $pattern = explode(';',$line);
    if ($i == 0){
      print <<<HEAD
     <tr class=warn>
        <td><b>$pattern[0]</td>
        <td>$pattern[1]</td>
        <td>$pattern[6]</td>
        <td class=info><b><font color=red>$pattern[7]*</td>
        <td>$pattern[4]</td>
        </tr>
HEAD;
    $i=1;
    }
    else{
        print <<<HEAD
      <tr class=warn>
        <td><b>$pattern[0]</td>
        <td>$pattern[1]</td>
        <td>$pattern[6]</td>
        <td class=info><b><font color=blue>$pattern[7]</td>
        <td>$pattern[4]</td>
        </tr>
HEAD;
   }
 }
 fclose($handle);
print <<<HEAD
</table>
</div>
<div class="col-xs-10">
<table class="table table-condensed table-hover">
<tr class=warning><td><font color=red>Resource Usage:</font></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/cpu.png"><font color=blue>cpu </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/memory.png"><font color=blue>memory </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/fcache.png"><font color=blue>fscache </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readtput.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writetput.png"><font color=blue>wtput </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readiops.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writeiops.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxtput.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxtput.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxpps.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxpps.png"><font color=blue>txpps</font></a></td>
</tr></table>
</div>
HEAD;
if (file_exists("$archives/compare-$family/$test/graphs/cpu-1.png")){
print <<<HEAD
<div class="col-xs-10">
<table class="table table-condensed table-hover">
<tr class=warning><td><font color=red>Resource Usage(6-9):</font></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/cpu-1.png"><font color=blue>cpu  </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/memory-1.png"><font color=blue>memory </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/fcache-1.png"><font color=blue>fscache </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readtput-1.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writetput-1.png"><font color=blue>wtput </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/readiops-1.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/writeiops-1.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxtput-1.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxtput-1.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/netrxpps-1.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$family/$test/graphs/nettxpps-1.png"><font color=blue>txpps</font></a></td>
</tr></table>
</div>
HEAD;
}
print <<<HEAD
<div class="col-xs-10">
<font color=red><small><i>* graphic package supports maximum of 5 data sources </i></small></font>
</div>
 <a name="$test"></a> <img src="$Archives/compare-$family/$test/$test.png" class="img-responsive" >
  </div>
 </div>
</div>

HEAD;
 }//foreach
}
?>
<!--- php code end -->
<?php include 'footer.php'; ?>
