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

    <title>AMI Benchmark Results</title>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/bootstrap-social.css" rel="stylesheet">
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
HEAD;
?>
