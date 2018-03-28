<?php
$ini_array = parse_ini_file("config.ini");

$results=$ini_array["results"];
$archives=($ini_array["archives"]);
// check if family, types and inst comparsion are set
if(isset($ini_array["family"])) $families=$ini_array["family"];
if(isset($ini_array["types"])) $types=$ini_array["types"];
if(isset($ini_array["inst"])) $inst=$ini_array["inst"];
//check if cpu, memory, specJVM2008, io and network tests are set
if(isset($ini_array["cputestname"])) $cputests=$ini_array["cputestname"];
if(isset($ini_array["memtestname"])) $memtests=$ini_array["memtestname"];
if(isset($ini_array["iotestname"])) $iotests=$ini_array["iotestname"];
if(isset($ini_array["javatestname"])) $javatests=$ini_array["javatestname"];
//chdir($results);
print <<<HEAD
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- the above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    
    <title> Benchmark Results</title>
    
        <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/bootstrap-social.css" rel="stylesheet">
      <!-- My stylesheet -->
    <link href="css/mystyles.css" rel="stylesheet">
<style> 
select {
    width: 50%;
    padding: 16px 20px;
    border: none;
    border-radius: 4px;
    background-color: #303F9F;
}
</style>
</head>
<body>
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                       <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="/AMIbench/index.php"><span class="glyphicon glyphicon-dashboard"></span> AutoBench</a>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                  <ul class="nav navbar-nav">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"  role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span>
                            CompareFamily <span class="caret"></span></a>
                       <ul class="dropdown-menu">
HEAD;
            
if (!empty($families)){
  print "<form action=\"comparefamily.php\" method=\"post\">";
  print "<div class=\"form-group\">";
  print " <select name=\"family\" class=\"selectpicker\">";
  //cpu tests 
  print " <optgroup  label=\"cpu tests\">";
  foreach ($families as $family){
     if (!empty($cputests)){
         print "<option class=\"form-control\" value=\"$family-cpu\">$family</option>";
    }
}
  print "</optgroup>";
  //memory tests
  print "<optgroup  label=\"memory tests\">";
  foreach ($families as $family){
     if (!empty($memtests)){
         print "<option class=\"form-control\" value=\"$family-mem\">$family</option>";
    }
}
  print " </optgroup> ";
  // java tests
  print "<optgroup  label=\"java tests\">";
  foreach ($families as $family){
     if (!empty($javatests)){
         print "<option class=\"form-control\" value=\"$family-jvm\">$family</option>";
    }
}
  print " </optgroup> ";
  print "<optgroup  label=\"io tests\">";
  foreach ($families as $family){
     if (!empty($iotests)){
         print "<option class=\"form-control\" value=\"$family-cpu\">$family</option>";
    }
}
  print " </optgroup> ";
  print "</select>";                          
}
  print "<button type=\"submit\" class=\"btn btn-primary\">Submit</button>";
  print "</div> </form>";

  print <<<HEAD
                         <li role="separator" class="divider"></li>
                      </ul>
                      </li>
			<li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"  role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span>
                            CompareType <span class="caret"></span></a> 
		       <ul class="dropdown-menu">
HEAD;
  if (!empty($types)){
  print "<form action=\"comparetype.php\" method=\"post\">";
  print "<div class=\"form-group\">";
  print " <select name=\"type\" class=\"selectpicker\">";
  //cpu tests 
  print " <optgroup  label=\"cpu tests\">";
  foreach ($types as $type){
     if (!empty($cputests)){
         print "<option class=\"form-control\" value=\"$type-cpu\">$type</option>";
    }
}
  print "</optgroup>";
  //memory tests
  print "<optgroup  label=\"memory tests\">";
  foreach ($types as $type){
     if (!empty($memtests)){
         print "<option class=\"form-control\" value=\"$type-mem\">$type</option>";
    }
}
  print " </optgroup> ";
  // java tests
  print "<optgroup  label=\"java tests\">";
  foreach ($types as $type){
     if (!empty($javatests)){
         print "<option class=\"form-control\" value=\"$type-jvm\">$type</option>";
    }
}
  print " </optgroup> ";
  print "<optgroup  label=\"io tests\">";
  foreach ($types as $type){
     if (!empty($iotests)){
         print "<option class=\"form-control\" value=\"$type-cpu\">$type</option>";
    }
}
  print " </optgroup> ";
  print "</select>";
}
  print "<button type=\"submit\" class=\"btn btn-primary\">Submit</button>";
  print "</div> </form>"; 
  print <<<HEAD
                       <li role="separator" class="divider"></li>
	 	      </ul>
		     </li>
		    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"  role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span>
                            Regression <span class="caret"></span></a>
                       <ul class="dropdown-menu">
HEAD;
  if (!empty($inst)){
  print "<form action=\"regress.php\" method=\"post\">";
  print "<div class=\"form-group\">";
  print " <select name=\"single\" class=\"selectpicker\">";
  foreach ($inst as $ins){
         print "<option class=\"form-control\" value=\"$ins\">$ins</option>";
    }
}
  print "</optgroup>";
  print "</select>";
  print "<button type=\"submit\" class=\"btn btn-primary\">Submit</button>";
  print "</div> </form>";
  print <<<HEAD
                        <li role="separator" class="divider"></li>
                      </ul>
		  </li>
       </ul>
     </div>
    </div>
</nav>
<header class="jumbotron">
  <div class="container-fluid">
    <div class="row row-header">
      <div class="col-xs-3">
      <!-- <img src="/AMIbench/img/benchmark-logo-wing.png" width="300"> -->
      <img src="http://www.psdgraphics.com/wp-content/uploads/2015/09/performance-meter.jpg" class="img-responsive">
      </div>
	<div class="col-xs-9">
	<p><b><font color="blue">AutoBench</font></b> is a framework for running benchmarks and reporting benchmark results in an automated fashion. Autobench generated results are useful for comparing performance of various AWS cloud <a href="https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/instance-types.html#AvailableInstanceTypes" target="_blank">instances </a> and identifying regression trends across baseAMI releases.  
<p>
Benchmarks can be executed manually. Better alternative is to use some CI/CD (Continues Integration and Deployment) platofrm. At Netflix, we use <a href="https://spinnaker.prod.netflix.net/#/applications/amibenchclient/executions" target="_blank">spinnikar</a>, that integrates well with <a href="https://www.tutorialspoint.com/jenkins/jenkins_overview.htm" target="_blank">Jenkins</a> and <a href="https://git-scm.com/" target="_blank">Git</a>, for triggering benchmarks at every BaseAMI release. Spinnaker deployment platform launches popular AWS instances using candidate release of BaseAMI with benchmark package baked into it. Benchmark results are dumped into a shared <a href=https://aws.amazon.com/efs/faq/" target=_blank">EFS</a> (NFS) mounted directly. Cron job is run once every week to process benchmark results and update links. Once the CI/CD pipeline is setup, no human intervension is required to run and process benchmark results. Updated results are accessed via autobench home page.
<p>
<i>All web pages also list AWS instance features, stored in <a href="https://sqlite.org/about.html" target="_blank">SQLite</a> database, for better comparison and context that help teams with instance selection decision for their service.</i> 
     </div>
 </div>
</div>
</header>
    <div class="container-fluid">
      <div class="row row-content">
        <div class="col-xs-2">
         <img src="https://alssl.askleomedia.com/wp-content/uploads/2009/01/cpu.jpg" class="img-responsive"> <h4>CPU Benchmarks</h4>
        </div>
       <div class="col-xs-10">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
		    <tr>
                        <th>CPU Tests</th>
                        <th>unit</th>
                        <th>URL</th>
		        <th>Brief Description</th>
                    </tr>
                    <tr>
                        <th><font color="white">sysbench</font></th>
                        <td>seconds</td>
                        <td><a href="https://wiki.gentoo.org/wiki/Sysbench" target="_blank">sysbench</a></td>
                        <td>Multithreaded cpu benchmark to perfom complex cpu calculations, like finding a prime numbers. <u><i>Less is better</i></u></td>
                    </tr>
			<tr>
                        <th><font color="white">lmbench</font></th>
                        <td>MHz</td>
                        <td><a href="http://www.bitmover.com/lmbench/mhz.8.html" target="_blank">lmbench</a></td>
                        <td>Single threaded cpu benchmark that calculates the processor clock rate and megahertz by performing unrolled, interlocked of adds or shifts. <u><i>More is better</i></u></td>
                    </tr>
			<tr>
                        <th><font color="white">Linux Kernel Build</font></th>
                        <td>seconds</td>
                        <td><a href="" target="_blank">buildkernel</a></td><td>Multithreaded cpu benchmark that builds Linux kernel downloaded from kernel.org (linux-4.9.10.tar.gz). <u><i>Less is better</i></u></td>
                    </tr>
		    <tr>
                        <th><font color="white">Open SSL</font> </th>
                        <td>signs/sec </td>
                        <td><a href="https://wiki.openwrt.org/doc/howto/benchmark.openssl" target="_blank">openssl </a></td>
                        <td>Multithreaded benchmark measures RSA 4096-bit performance of OpenSSL. OpenSSL implements SSL (Secure Sockets Layer) and TLS (Transport Layer Security) protocols. Offers rough estimate of OpenSSL performance. <u><i> More is better</i></u></td>
		   </tr>
		      <tr>
                        <th><font color="white">MP3 Encoding</font></th>
                        <td>seconds</td>
                        <td><a href="http://openbenchmarking.org/innhold/de22f2ece9110e1e1d4bd7bcf4d94d87be312b06" target="_blank">encode-mp3</a></td>
                        <td>Singlethreaded benchmark measures the time required to encode a WAV file to MP3 format using LAME MP3 encoder. <u><i> Less is better</i></u></td>
                   </tr>
		       <tr>
                        <th><font color="white">ffmpeg</font></th>
                        <td>seconds</td>
                        <td><a href="http://openbenchmarking.org/innhold/831fd0f37175fda1045b50080ce8121553f0b9f9" target="_blank">ffmpeg 2.5.0</a></td>
                        <td>Multithreaded benchmark measures system's audio/video performance using ffmpeg. Test uses a sample H.264 format video file for conversion to test system's encoding performance. <u><i> Less is better</i></u> </td>
                   </tr>
                    <tr>
                        <th><font color="white">7-zip Compression</font></th>
                        <td>MIPS</td>
                        <td><a href="http://www.7-zip.org/7z.html" target="_blank">7zip-1.6.2</a></td>
                        <td>Multithreaded benchmark uses p7zip integrated compression feature to test 7-zip performance. Popular applications that support 7z archives: WinRAR, PowerArchiver, TUGZip, IZArc..<u><i> More is better</i></u> </td>
                   </tr>
               </table>
             </div>
       </div>
        </div>
        <div class="row row-content">
            <div class="col-xs-2">
                <img src="http://previews.123rf.com/images/alexlmx/alexlmx1509/alexlmx150900283/45643576-2-Gb-RAM-or-ROM-memory-chip-for-smartphone-and-tablet-concept-Stock-Photo.jpg" class="img-responsive"><h4>MEM Benchmarks</h4>
            </div>
            <div class="col-xs-10">
            <div class="table-responsive">
                <table class="table table-striped">
		   <tr>
                        <th>Memory Tests</th>
                        <th>unit</th>
                        <th>URL</th>
                        <th>Brief Description</th>
                    </tr>
                    <tr>
                        <th><font color="white">sysbench</font></th>
                        <td>ops</td>
                        <td><a href="https://wiki.gentoo.org/wiki/Sysbench" target="_blank">sysbench</a></td>
                        <td>Multithreaded benchmark to measure memory ops/sec and tput improvement as number of cpus are increased. <u><i> More is better</i></u></td>
                    </tr>
                    <tr>
                        <th><font color="white">lmbench</font> </th>
                        <td>nanoseconds</td>
                        <td><a href="http://www.bitmover.com/lmbench/lat_mem_rd.8.html" target="_blank">lat_mem_rd </a></td>
                        <td> memory latency benchmark that measures read latency for varying memory sizes and strides.Latency destribution is reported across cpu internal (L1/L2), external (L3) and main memory. <u><i> Less is better</i></u></td>
                   </tr>
		   <tr>
                        <th><font color="white">lmbench</font> </th>
                        <td>MB/s</td>                        
			<td><a href="https://fossies.org/linux/lmbench/doc/bw_mem.8" target="_blank">bw_mem </a></td>
                        <td>Measures memory bandwidth for a variety of memory operations, such as read, write, and copy. Results are reported in megabytes per second.<u><i> More is better</i></u></td>
                   </tr>
		   <tr>
                        <th><font color="white">cachebench</font> </th>
                        <td>MB/s</td>                        
			<td><a href="http://icl.cs.utk.edu/llcbench/cachebench.html" target="_blank">cachebench </a></td> <td>CacheBench, part of LLCbench, is designed to test the main memory and cache bandwidth performance. Memory read, write and modified operations are tested. <u><i> More is better</i></u></td>
                   </tr>
		  <tr>                        
		    <th><font color="white">stream</font> </th>
                    <td>MB/s</td>                        
                    <td><a href="https://www.cs.virginia.edu/stream/ref.html" target="_blank">stream </a></
td> <td>Measures server memory bandwidth performance by performing various memory operations: COPY, SCALE, SUM, TRIAD. <u><i> More is better</i></u></td>
                   </tr>
               </table>
             </div>
            </div>
        </div>
            <div class="row row-content">
            <div class="col-xs-2">
                <img src="http://previews.123rf.com/images/funkypoodle/funkypoodle0806/funkypoodle080600013/3184844-Network-connection-plug-close-up-Stock-Photo.jpg" class="img-responsive"><h4>Network Benchmarks</h4>
            </div>
            <div class="col-xs-10">
	     <div class="table-responsive">
                <table class="table table-striped">
		  <tr>
                        <th>Network Tests</th>
                        <th>unit</th>
                        <th>URL</th>
                        <th>Brief Description <font color=red>(Not implemented)</font></th>
                    </tr>
		    <tr>
                    <th><font color="white">netperf </font></th>
                    <td>Mbps</td>
                    <td><a href="http://www.netperf.org/svn/netperf2/tags/netperf-2.7.0/doc/netperf.html" target="_blank">netperf </a></
td> <td>Measure network throughput <u><i> More is better</i></u></td>
                   </tr>
		   <tr>
		   <th><font color="white">netperf </font></th>
                    <td>TPS</td>
                    <td><a href="http://www.netperf.org/svn/netperf2/tags/netperf-2.7.0/doc/netperf.html" target="_blank">netperf </a></td> <td>Measure Latency by measure TCP transactions per seconds. Single transaction is active at any given time. More transaction means lower network latencies. <u><i> More is better</i></u></td>
                   </tr>
		   <tr>
                   <th><font color="white">wrk</font> </th>
                    <td>PPS</td>                    <td><a href="https://github.com/wg/wrk" target="_blank">wrk </a></td> <td>Use HTTP benchmark tool, wrk, to perform http transaction with 1 byte payload against ngnix webserver. Measure bi-directional packet per second rate <u><i>. More is better</i></u></td>
                   </tr>
               </table>
             </div>
            </div>
        </div>
            <div class="row row-content">
            <div class="col-xs-2">
                <img src="https://images-na.ssl-images-amazon.com/images/G/01/electronics/detail-page/B0037NYKW6.app.2tb.jpg" class="img-responsive"><h4>Storage Benchmarks</h4>
            </div>
            <div class="col-xs-10">
		<div class="table-responsive">
                <table class="table table-striped">
		    <tr>
                        <th>Storage Tests</th>
                        <th>unit</th>
                        <th>URL</th>
                        <th>Brief Description <font color=red>(Not implemented)</font></th>
                    </tr>
		<tr>
                   <th><font color="white">fio< /font> </th>
                    <td>MB/s</td>
                    <td><a href="http://bluestop.org/files/fio/README.txt" target="_blank">fio </a></td> <td>Measure storage throughput of direct or instance attach storage (ephemeral). Higher throughput is required when workload is streaming or have sequential access pattern. Large block size of 128 KB is tested using a direct IO path on xfs file system  <u><i> More is better</i></u></td>
                   </tr>
		  <tr>
                   <th><font color="white">fio</font> </th>
                    <td>iops</td>                    <td><a href="http://bluestop.org/files/fio/README.txt" target="_blank">fio </a></td> <td>Measure storage IOPS of direct or instance attach storage (ephemeral). Higher iops is needed when workload is latency sensitive and have random access pattern. Small block size of 4 KB is tested using a direct IO path on xfs file system  <u><i> More is better</i></u></td>
                   </tr>
               </table>
             </div>
            </div>
          </div>
        </div>
      <div class="row row-content">
            <div class="col-xs-2">
                <img src="https://www.uwc.ac.za/Faculties/EMS/SOG/PublishingImages/online-application.jpg" class="img-responsive"><h4>Application Benchmarks</h4>
            </div>
            <div class="col-xs-10">
                <div class="table-responsive">
                <table class="table table-striped">
		<tr>
                        <th><font color="white">specjvm2008</font></a></th>
                        <th>unit</th>
                        <th>URL</th>
                        <th>Brief Description</th>
                    </tr>
                <tr>
                   <th><font color="white">SpecJVM2008 Benchmarks</font> </th>
                    <td>ops/m</td>
                <td><a href="https://www.spec.org/jvm2008/docs/UserGuide.html#GeneralConcepts" target="_blank">specjvm2008 </a></td> <td>SPECjvm2008 comprises a collection of workloads intended to represent a diverse set of common types of computation. Measure performance of a JRE (a JVM and associated libraries)and performance of the operating system and hardware in the context of executing the JRE. Bencharks available are listed below:</td>
                </tr>
		<tr>
                   <th><font color="white">Compiler</font> </th>
                    <td>ops/m</td>                    
		<td><a href="https://www.spec.org/jvm2008/docs/benchmarks/index.html" target="_blank">Compiler </a></td> <td>java compiler benchmark. This benchmark uses JDK front end compiler to compile .java files<u><i> More is better</i></u></td>
                </tr>
                <tr>
                   <th><font color="white">Compress</font> </th>
                    <td>ops/m</td>
                <td><a href="https://www.spec.org/jvm2008/docs/benchmarks/index.html" target="_blank">Compress </a></td> <td>This benchmark compresses data using a modified Lempel-Ziv method (LZW). Basically finds common substrings and replaces them with a variable size code. This is deterministic, and can be done on the fly. Thus, the decompression procedure needs no input table, but tracks the way the table was built. <u><i> More is better</i></u></td>
                </tr>
		<tr>
                   <th><font color="white">Crypto</font> </th>
                    <td>ops/m</td>
                <td><a href="https://www.spec.org/jvm2008/docs/benchmarks/index.html" target="_blank">Crypto </a></td> <td>This benchmark focuses on different areas of crypto and are split in three different sub-benchmarks. The different benchmarks use the implementation inside the product and will therefore focus on both the vendor implementation of the protocol as well as how it is executed. <p>

 <ul><b>aes</b> encrypt and decrypt using the AES and DES protocols, using CBC/PKCS5Padding and CBC/NoPadding. Input data size is 100 bytes and 713 kB.</ul>
 <ul><b>rsa </b> encrypt and decrypt using the RSA protocol, using input data of size 100 bytes and 16 kB. </ul>
 <ul><b>signverify</b> sign and verify using MD5withRSA, SHA1withRSA, SHA1withDSA and SHA256withRSA protocols. Input data size of 1 kB, 65 kB and 1 MB.</ul>    <p><u><i> More is better</i></u></td>
 
                </tr>
		<tr>
                   <th><font color="white">Derby</font> </th>
                    <td>ops/m</td>
                <td><a href="https://www.spec.org/jvm2008/docs/benchmarks/index.html" target="_blank">Derby </a></td> <td>This benchmark uses an open-source database written in pure Java. It is synthesized with business logic to stress the BigDecimal library. The focus of this benchmark is on BigDecimal computations (based on telco benchmark) and database logic, especially, on locks behavior   <u><i> More is better</i></u></td>
                </tr>
		<tr>
                   <th><font color="white">MPEGaudio</font> </th>
                    <td>ops/m</td>
                <td><a href="https://www.spec.org/jvm2008/docs/benchmarks/index.html" target="_blank">MPEGaudio </a></td><td>This benchmark is floating-point heavy and a good test of mp3 decoding   <u><i> More is better</i></u></td>
                </tr>
		<tr>
                   <th><font color="white">Scimark</font> </th>
                    <td>ops/m</td>
                <td><a href="https://www.spec.org/jvm2008/docs/benchmarks/index.html" target="_blank">Scimark </a></td><td> floating point benchmark. Each of the subtests (fft, lu, monte_carlo, sor, sparse) were incorporated into SPECjvm2008. There are two versions of this test, one with a "large" dataset (32Mbytes) which stresses the memory subsystem and a "small" dataset which stresses the JVMs (512Kbytes).  <u><i> More is better</i></u></td>
                </tr>
		<tr>
                   <th><font color="white">Serial</font> </th>
                    <td>ops/m</td>
                <td><a href="https://www.spec.org/jvm2008/docs/benchmarks/index.html" target="_blank">Serial </a></td><td>This benchmark serializes and deserializes primitives and objects, using data from the JBoss benchmark. The benchmark has a producer-consumer scenario where serialized objects are sent via sockets and deserialized by a consumer on the same system. The benchmark heavily stress the Object.equals() test.   <u><i> More is better</i></u></td>
                </tr>
		<tr>
                   <th><font color="white">Sunflow</font> </th>
                    <td>ops/m</td>
                <td><a href="https://www.spec.org/jvm2008/docs/benchmarks/index.html" target="_blank">Sunflow </a></td><td>This benchmark tests graphics visualization using an open source, internally multi-threaded global illumination rendering system. The sunflow library is threaded internally, i.e. it's possible to run several bundles of dependent threads to render an image. The number of internal sunflow threads is required to be 4 for a compliant run. It is however possible to configure in property specjvm.benchmark.sunflow.threads.per.instance, but no more than 16, per sunflow design. Per default, the benchmark harness will use half the number of benchmark threads, i.e. will run as many sunflow benchmark instances in parallel as half the number of hardware threads. This can be configured in specjvm.benchmark.threads.sunflow.  <u><i> More is better</i></u></td>
                </tr>
		<tr>
                   <th><font color="white">XML</font> </th>
                    <td>ops/m</td>
                <td><a href="https://www.spec.org/jvm2008/docs/benchmarks/index.html" target="_blank">XML </a></td> <td> This benchmark has two sub-benchmarks: XML.transform and XML.validation. XML.transform exercises the JRE's implementation of javax.xml.transform (and associated APIs) by applying style sheets (.xsl files) to XML documents. XML.validation exercises the JRE's implementation of javax.xml.validation (and associated APIs) by validating XML instance documents against XML schemata (.xsd files). The schemata and XML documents are several real life examples that vary in size (1KB to 607KB) and in the XML schema features that are used most heavily. One "operation" of XML.validation consists of processing each style sheet / document pair, accessing the XML document as a DOM source and a SAX source. <u><i> More is better</i></u></td>
                </tr>
               </table>
             </div>

             </div>

            </div>
        </div>
    <footer class="row-footer">
        <div class="container-fluid">
            <div class="row">             
                <div class="col-xs-4">
                    <ul class="list-unstyled">
                        <li><a href="/AMIbench/index.php">Home</a></li>
                    </ul>
		</div>
		<div class="col-xs-4">
                    <ul class="list-unstyled">
                        <li><a href="/Archives">Archives</a></li>
                    </ul>
                </div>

		<div class="col-xs-4">
	        <form>
          	<div class="input-group">
           		<input type="text" class="form-control" placeholder="Search">
            	<div class="input-group-btn">
            		 <button class="btn btn-default" type="submit">
              			<i class="glyphicon glyphicon-search"></i>
             		</button>
		</div>
            </div>
          </div>
	<div class="container-fluid">
	 <div class="row">
          <div class="col-xs-12">
                    <p align=center>© Netflix Inc.</p>
            </div>
          </div>
        </div>
    </footer>
<script>
function validate()
        {
            var selectChoose = document.getElementById('choose');
            var maxOptions = 2;
            var optionCount = 0;
            for (var i = 0; i < selectChoose.length; i++) {
                if (selectChoose[i].selected) {
                    optionCount++;
                    if (optionCount > maxOptions) {
                        alert("Please select two instances only ")
                        return false;
                    }
                }
            }
            return true;
        }
</script>
   <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
HEAD;
?>
