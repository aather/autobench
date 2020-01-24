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
if(isset($ini_array["s3testname"])) $s3tests=$ini_array["s3testname"];
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
 // io tests
 print "<optgroup  label=\"io tests\">";
  foreach ($families as $family){
     if (!empty($iotests)){
         print "<option class=\"form-control\" value=\"$family-io\">$family</option>";
    }
}
  print " </optgroup> ";
  // s3 tests
  print "<optgroup  label=\"s3 tests\">";
  foreach ($families as $family){
     if (!empty($s3tests)){
         print "<option class=\"form-control\" value=\"$family-s3\">$family</option>";
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
  // io tests
  print "<optgroup  label=\"io tests\">";
  foreach ($types as $type){
     if (!empty($iotests)){
         print "<option class=\"form-control\" value=\"$type-io\">$type</option>";
    }
}
  print " </optgroup> ";
  // s3 tests
  print "<optgroup  label=\"s3 tests\">";
  foreach ($types as $type){
     if (!empty($s3tests)){
         print "<option class=\"form-control\" value=\"$type-s3\">$type</option>";
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
	<p><b><font color="blue">AutoBench</font></b> uses open source <a href="http://www.phoronix-test-suite.com/documentation/phoronix-test-suite.html" target="_blank">phoronix test suites</a> framework to run various benchmarks. Benchmarks are run in an automated fashion via <a href="https://spinnaker.prod.netflix.net/#/applications/amibenchclient/executions" target="_blank">spinnikar pipeline</a> by launching popular instances. Spinnaker pipe line is triggerd whenever baseAMI is promoted to pre-candidate stage. All launched instances running benchmarks dump results into a shared EFS (NFS) mounted directory. Results from previous runs are aggregated and merged to generate reports. Results can be used to compare instance performance or highlight any regression introduced into new BaseAMI release. Having historical benchmark results available for comparision helps identify regression trends that may slowly sneaks into baseAMI releases.
<p>
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
