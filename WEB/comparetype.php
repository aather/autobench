<?php include 'menu.php'; ?>
<?php
$ini_array = parse_ini_file("config.ini");
$results=$ini_array["results"];
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

$found=$_POST["type"]; 

if (strpos($found, "mem")){
   $type= chop ($found,"mem");
   $type= chop ($type,"-");
}
else if (strpos($found, "jvm")){
   $type= chop ($found,"jvm");
   $type= chop ($type,"-");
}
else if (strpos($found, "io")){
   $type= chop ($found,"io");
   $type= chop ($type,"-");
}
else if (strpos($found, "s3")){
   $type= chop ($found,"s3");
   $type= chop ($type,"-");
}
else {
   $type= chop ($found,"cpu");
   $type= chop ($type,"-");
 }

chdir ("/efs/html/AMIbench");
try
{
    //open the database
    $db = new PDO('sqlite:AMIbench.sqlite');

    //now output the data to a simple html table...
    print <<<HEADER
<div class="container-fluid">
  <div class="row row-content">
     <div class="col-xs-12">
      <div class="table-responsive">
       <table class="table table-striped table-hover">
        <tr class=warn>
        <th class=warn>inst</th>
        <th class=warn>model</th>
        <th class=warn>vcpu</th>
        <th class=warn>memory(GB)</th>
        <th class=warn>storage(GB)</th>
	<th><a href="https://confluence.netflix.com/display/CLDPERF/AWS+Instances+Network+Throughput" target="_blank">network(Mbps)</a></th>
        <th class=warn><a href="https://aws.amazon.com/about-aws/whats-new/2016/06/introducing-elastic-network-adapter-ena-the-next-generation-network-interface-for-ec2-instances/" target="_blank">ena</a></th>
	<th class=warn><a href="https://confluence.netflix.com/display/CLDPERF/AWS+I2+and+I3+Instant+Types+Comparison" target="_blank">nvme</a></th>
        <th class=warn><a href="https://aws.amazon.com/ec2/faqs/#enhanced-networking" target="_blank">sriov</a></th>
        <th class=warn><a href="http://techblog.cloudperf.net/2016/05/2-million-packets-per-second-on-public.html" target="_blank">xennet</a></th>
        <th class=warn><a href="http://docs.aws.amazon.com/AWSEC2/latest/UserGuide/EBSOptimized.html" target="_blank">ebsopt(tput:iops)</a></th>
        <th class=warn><a href="http://techblog.cloudperf.net/2016/09/exploring-numa-on-amazon-cloud-instances.html" target="_blank">numa</a></th>
        <th class=warn> price</th>
        </tr>
HEADER;
    $stmt = $db->prepare("SELECT * FROM Instype WHERE name LIKE ?");
    $stmt->execute(["%-$type%"]); //i2-xlarge
    while($row = $stmt->fetch())
    {
         print "<tr>";
         print " <th class=warn>" .$row['name']. "</th>";
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
         #print " <td>" . $row['price']. "</td>";
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
/**adjust scale to desired size,
add browser prefixes**/
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
 $sorted = "$archives/compare-$type/$test/testreportsorted";
 $testreport = "$archives/compare-$type/$test/report";
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
  else { 	/* if file exists and not zero */
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
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/cpu.png"><font color=blue>cpu </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/memory.png"><font color=blue>memory</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/fcache.png"><font color=blue>fscache</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readtput.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writetput.png"><font color=blue>wtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readiops.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writeiops.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxtput.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxtput.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxpps.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxpps.png"><font color=blue>txpps</font></a></td>
</tr></table>
</div>
HEAD;
if (file_exists("$archives/compare-$type/$test/graphs/cpu-1.png")){
print <<<HEAD
<div class="col-xs-10">
<table class="table table-condensed table-hover">
<tr class=warning><td><font color=red>System Metrics:</font></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/cpu-1.png"><font color=blue>cpu </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/memory-1.png"><font color=blue>memory</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/fcache-1.png"><font color=blue>fscache</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readtput-1.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writetput-1.png"><font color=blue>wtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readiops-1.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writeiops-1.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxtput-1.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxtput-1.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxpps-1.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxpps-1.png"><font color=blue>txpps</font></a></td>
</tr></table>
</div>
HEAD;
}
print <<<HEAD
<div class="col-xs-10">
<font color=red><small><i>* graphic package supports maximum of 5 data sources </i></small></font>
</div>
 <a name="$test"></a> <img src="$Archives/compare-$type/$test/$test.png" class="img-responsive" >
  </div>
 </div>
</div>
HEAD;
 }//foreach
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
$sorted = "$archives/compare-$type/$test/testreportsorted";
$testreport = "$archives/compare-$type/$test/report";
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
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/cpu.png"><font color=blue>cpu </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/memory.png"><font color=blue>memory</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/fcache.png"><font color=blue>fscache</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readtput.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writetput.png"><font color=blue>wtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readiops.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writeiops.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxtput.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxtput.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxpps.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxpps.png"><font color=blue>txpps</font></a></td>
</div>
HEAD;
if (file_exists("$archives/compare-$type/$test/graphs/cpu-1.png")){
print <<<HEAD
<div class="col-xs-10">
<table class="table table-condensed table-hover">
<tr class=warning><td><font color=red>System Metrics:</font></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/cpu-1.png"><font color=blue>cpu </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/memory-1.png"><font color=blue>memory</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/fcache-1.png"><font color=blue>fscache</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readtput-1.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writetput-1.png"><font color=blue>wtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readiops-1.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writeiops-1.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxtput-1.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxtput-1.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxpps-1.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxpps-1.png"><font color=blue>txpps</font></a></td>
</tr></table>
</div>
HEAD;
}
print <<<HEAD
<div class="col-xs-10">
<font color=red><small><i>* graphic package supports maximum of 5 data sources </i></small></font>
</div>
 <a name="$test"></a> <img src="$Archives/compare-$type/$test/$test.png" class="img-responsive" >
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
      <th>Java Version</th>
      <th>Unit</th>
      <th>Value</th>
      <th>Test | Instance Type | Kernel Version</th>
     </tr>
HEAD;
$sorted = "$archives/compare-$type/$test/testreportsorted";
$testreport = "$archives/compare-$type/$test/report";
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
<tr class=warning><td><font color=red>System Metrics:</font></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/cpu.png"><font color=blue>cpu </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/memory.png"><font color=blue>memory</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/fcache.png"><font color=blue>fscache</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readtput.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writetput.png"><font color=blue>wtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readiops.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writeiops.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxtput.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxtput.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxpps.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxpps.png"><font color=blue>txpps</font></a></td>
</tr></table>
</div>
HEAD;
if (file_exists("$archives/compare-$type/$test/graphs/cpu-1.png")){
print <<<HEAD
<div class="col-xs-10">
<table class="table table-condensed table-hover">
<tr class=warning><td><font color=red>System Metrics:</font></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/cpu-1.png"><font color=blue>cpu </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/memory-1.png"><font color=blue>memory</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/fcache-1.png"><font color=blue>fscache</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readtput-1.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writetput-1.png"><font color=blue>wtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readiops-1.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writeiops-1.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxtput-1.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxtput-1.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxpps-1.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxpps-1.png"><font color=blue>txpps</font></a></td>
</tr></table>
</div>
HEAD;
}
print <<<HEAD
<div class="col-xs-10">
<font color=red><small><i>* graphic package supports maximum of 5 data sources </i></small></font>
</div>
 <a name="$test"></a> <img src="$Archives/compare-$type/$test/$test.png" class="img-responsive" >
  </div>
 </div>
</div>
HEAD;
 }//foreach
}//if
else if (strpos($found, "io")){
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
      <th>Java Version</th>
      <th>Unit</th>
      <th>Value</th>
      <th>Test | Instance Type | Kernel Version</th>
     </tr>
HEAD;
$sorted = "$archives/compare-$type/$test/testreportsorted";
$testreport = "$archives/compare-$type/$test/report";
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
<tr class=warning><td><font color=red>System Metrics:</font></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/cpu.png"><font color=blue>cpu </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/memory.png"><font color=blue>memory</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/fcache.png"><font color=blue>fscache</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readtput.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writetput.png"><font color=blue>wtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readiops.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writeiops.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxtput.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxtput.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxpps.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxpps.png"><font color=blue>txpps</font></a></td>
</tr></table>
</div>
HEAD;
if (file_exists("$archives/compare-$type/$test/graphs/cpu-1.png")){
print <<<HEAD
<div class="col-xs-10">
<table class="table table-condensed table-hover">
<tr class=warning><td><font color=red>System Metrics:</font></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/cpu-1.png"><font color=blue>cpu </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/memory-1.png"><font color=blue>memory</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/fcache-1.png"><font color=blue>fscache</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readtput-1.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writetput-1.png"><font color=blue>wtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readiops-1.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writeiops-1.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxtput-1.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxtput-1.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxpps-1.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxpps-1.png"><font color=blue>txpps</font></a></td>
</tr></table>
</div>
HEAD;
}
print <<<HEAD
<div class="col-xs-10">
<font color=red><small><i>* graphic package supports maximum of 5 data sources </i></small></font>
</div>
 <a name="$test"></a> <img src="$Archives/compare-$type/$test/$test.png" class="img-responsive" >
  </div>
 </div>
</div>
HEAD;
 }//foreach
}//if
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
$sorted = "$archives/compare-$type/$test/testreportsorted";
$testreport = "$archives/compare-$type/$test/report";
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
<tr class=warning><td><font color=red>System Metrics:</font></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/cpu.png"><font color=blue>cpu </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/memory.png"><font color=blue>memory</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/fcache.png"><font color=blue>fscache</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readtput.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writetput.png"><font color=blue>wtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readiops.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writeiops.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxtput.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxtput.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxpps.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxpps.png"><font color=blue>txpps</font></a></td>
</tr></table>
</div>
HEAD;
if (file_exists("$archives/compare-$type/$test/graphs/cpu-1.png")){
print <<<HEAD
<div class="col-xs-10">
<table class="table table-condensed table-hover">
<tr class=warning><td><font color=red>System Metrics:</font></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/cpu-1.png"><font color=blue>cpu </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/memory-1.png"><font color=blue>memory</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/fcache-1.png"><font color=blue>fscache</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readtput-1.png"><font color=blue>rtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writetput-1.png"><font color=blue>wtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/readiops-1.png"><font color=blue>riops </font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/writeiops-1.png"><font color=blue>wiops</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxtput-1.png"><font color=blue>rxtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxtput-1.png"><font color=blue>txtput</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/netrxpps-1.png"><font color=blue>rxpps</font></a></td>
<td><a target="_blank" href="http:$Archives/compare-$type/$test/graphs/nettxpps-1.png"><font color=blue>txpps</font></a></td>
</tr></table>
</div>
HEAD;
}
print <<<HEAD
<div class="col-xs-10">
<font color=red><small><i>* graphic package supports maximum of 5 data sources </i></small></font>
</div>
 <a name="$test"></a> <img src="$Archives/compare-$type/$test/$test.png" class="img-responsive" >
  </div>
 </div>
</div>
HEAD;
 }//foreach
}//if
?>
<!--- php code end -->
<?php include 'footer.php'; ?>
