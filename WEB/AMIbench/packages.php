<?php include 'menu.php'; ?>
<?php
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

$packages = "/efs/amibench/PACKAGES";
chdir ($packages);

$found=$_POST["packages"]; 
if (preg_match("/previous/", $found, $match)){
//if ($found == "previous:changed"){
   $old = "PREVIOUS-PACKAGES";
}
// Find the name of file (4.4.0-71-generic.pkg) that we are asked to compare
//else if (strpos($found, "changed")){  
else if (preg_match("/changed/", $found, $match)){
   $old = chop ($found,"changed");
   $old = chop ($old,":");
}
//else if (strpos($found, "newer")){
else if (preg_match("/newer/", $found, $match)){
   $old= chop ($found,"newer");
   $old= chop ($old,":");
}
else {
   $old= chop ($found,"obseleted");
   $old= chop ($old,":");
 }

//New file. It is always be LATEST-PACKAGES
$new = "LATEST-PACKAGES";
// file to compare. Selected option is PREVIOUS-TUNABLES or previous

$nfh = fopen($new, 'r');
$ofh = fopen($old, 'r');

$nData = fread($nfh, filesize($new));
$oData = fread($ofh, filesize($old));

$new_assoc_array = array();
$old_assoc_array = array();

$new_array = explode("\n", $nData);
$old_array = explode("\n", $oData);

foreach($new_array as $line){
    $line_array = explode(":", $line);
    if (($line_array[1] != "")){
      $new_assoc_array[$line_array[0]] = $line_array[1];
     }
}
foreach($old_array as $line){
    $line_array = explode(":", $line);
    if (($line_array[1] != "")){
      $old_assoc_array[$line_array[0]] = $line_array[1];
     }
}
fclose($nfh);
fclose($ofh);

//if (strpos($found, "changed")){
if (preg_match("/changed/", $found, $match)){
  /* 
   * Return matching packages (keys) that we can use to compare 
   * packages values to find what has changed 
   */
   $newkeyvalues = array_intersect_key($new_assoc_array, $old_assoc_array);
   $oldkeyvalues = array_intersect_key($old_assoc_array, $new_assoc_array);
   $ver = "Kernel Version";

  /* 
   * We have two arrays (new and old) with same keys but different values
   * Routine will compare each key/value pair and return the one that is changed 
   * in new release
   */
   $newtunablevalues = array_diff_assoc($newkeyvalues,$oldkeyvalues);
   // get keys and values from $newtunablesvalues array
   $newkeys = array_keys($newtunablevalues);
   $newvalues = array_values($newtunablevalues);
   // get values only from $oldtunablesvalues array
   $oldtunablevalues = array_diff_assoc($oldkeyvalues,$newkeyvalues);
   $oldkeys = array_keys($oldtunablevalues);
   $oldvalues = array_values($oldtunablevalues);
   //print_r($newtunablevalues);

   // Now print them in a table.
print <<<HEAD
<div class="container-fluid">
  <div class="row row-content">
    <div class="col-xs-12">
     <div class="table-responsive">
       <table class="table table-striped table-hover">
        <tr class="info">
        <td><b><font color="red">Kernel Version</font></b></td>
        <td><b><font color="red">$new_assoc_array[$ver]</font></b></td>
        <td><b><font color="red">$old_assoc_array[$ver]</font></b></td>
        </tr>
HEAD;
    
      for ($i=0; $i<count($newkeys); ++$i){
       //foreach($newtunablevalues as $x => $x_value) {
print <<<HEAD
        <tr class="warn">
        <td><font color="brown">$newkeys[$i]</font></td>
        <td><font color="blue">$newvalues[$i]</font></td>
        <td><font color="green">$oldvalues[$i]</font></td>
        </tr>
HEAD;
  }
print <<<HEAD
      </table>
     </div>
    </div>
  </div>
</div> 
HEAD;
}
//else if($found == "new"){
//else if (strpos($found, "newer")){
else if (preg_match("/newer/", $found, $match)){
   /*
    * If the first array is new or newer release, routine will return 
    * array that contains keys (tunables) that are newly added into 
    * the kernel
    */
   $results = array_diff_key($new_assoc_array,$old_assoc_array);
   $ver = "Kernel Version";
print <<<HEAD
<div class="container-fluid">
  <div class="row row-content">
    <div class="col-xs-12">
     <div class="table-responsive">
       <table class="table table-striped table-hover">
        <tr class="info">
        <td><b><font color="red">Kernel Version - New Packages</font></b></td>
        <td><b><font color="red">$new_assoc_array[$ver]</font></b></td>
        </tr>
HEAD;
       foreach($results as $key => $value) {
print <<<HEAD
        <tr class="warn">
        <td><font color="brown">$key</font></td>
        <td><font color="blue">$value</font></td>
        </tr>
HEAD;
  }
print <<<HEAD
      </table>
     </div>
    </div>
  </div>
</div>
HEAD;
}
else {
  /*
   *  If first array is old or older release, routine will return array that 
   *  contains keys (tunables) that are no longer available or obsoleted in 
   * the new kernel release
   */
   $results = array_diff_key($old_assoc_array,$new_assoc_array);
   $ver = "Kernel Version";
print <<<HEAD
<div class="container-fluid">
  <div class="row row-content">
    <div class="col-xs-12">
     <div class="table-responsive">
       <table class="table table-striped table-hover">
        <tr class="info">
        <td><b><font color="red">Kernel Version - Obseleted Packages</font></b></td>
        <td><b><font color="red">$old_assoc_array[$ver]</font></b></td>
        </tr>
HEAD;
      //for ($i=0; $i<count($newkeys); ++$i){
      foreach($results as $key => $value) {
print <<<HEAD
	<tr class="warn">
        <td><font color="brown">$key</font></td>
        <td><font color="blue">$value</font></td>
        </tr>
HEAD;
  }
print <<<HEAD
      </table>
     </div>
    </div>
  </div>
</div>
HEAD;
 }
?>
<!--- php code end -->
<?php include 'footer.php'; ?>
