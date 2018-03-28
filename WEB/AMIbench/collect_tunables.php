<?php
global $file_info; 
$file_info = array();

/**
 * 
 * @function recursive_scan
 * @description Recursively scans a folder and its child folders
 * @param $path :: Path of the folder/file
 * 
 * */
function recursive_scan($path){
    global $file_info;
    $path = rtrim($path, '/');
    if ((!is_dir($path)) && (!is_link($path))) 
       $file_info[] = $path;
     elseif (!is_link($path)) {
          $files = scandir($path);
          foreach($files as $file) if($file != '.' && $file != '..') recursive_scan($path . '/' . $file);
        }
}

recursive_scan('/proc/sys');
foreach ($file_info as $tunable){
     exec("sudo cat $tunable", $output);
     foreach ($output as $out){
        $out = preg_replace('/\s/', '', $out); // remove spaces from string 
        $tunable = preg_replace('/^\//', '', $tunable); // replace first slash with space in  string 
        $tunable = preg_replace('/\//', '-', $tunable); // replace slash with - in  string 
	print "$tunable:$out\n";
     }
     $output="";
}

$file_info="";

recursive_scan('/sys/kernel/mm');
foreach ($file_info as $tunable){
     exec("sudo cat $tunable", $output);
     foreach ($output as $out){
        $out = preg_replace('/\s/', '', $out); // remove spaces from string 
        $tunable = preg_replace('/^\//', '', $tunable); // replace first slash with space in  string 
        $tunable = preg_replace('/\//', '-', $tunable); // remove spaces from string 
        print "$tunable:$out\n";
     }
     $output="";
}

$file_info="";

recursive_scan('/sys/devices');
foreach ($file_info as $tunable){
     exec("sudo cat $tunable", $output);
     foreach ($output as $out){
        $out = preg_replace('/\s/', '', $out); // remove spaces from string 
        $tunable = preg_replace('/^\//', '', $tunable); // replace first slash with space in  string 
        $tunable = preg_replace('/\//', '-', $tunable); // remove spaces from string 
        print "$tunable:$out\n";
     }
     $output="";
}

exec("sudo blockdev --report", $blockdev);
foreach  ($blockdev as $dev){
   $dev = preg_replace('/\//', '-', $dev); // replace slash with - in  string
   //$dev = explode(" ", $dev);
   $dev = preg_split('/\s+/', $dev);
   if (preg_match("/$dev[0]/","RO",$matches))
     continue;
   print "blockdev$dev[6]-RO:$dev[0]\n";
   print "blockdev/$dev[6]-RA:$dev[1]\n";
   print "blockdev/$dev[6]-SSZ:$dev[2]\n";
   print "blockdev/$dev[6]-BSZ:$dev[3]\n";
   print "blockdev/$dev[6]-StartSec:$dev[4]\n";
   print "blockdev/$dev[6]-Size:$dev[5]\n";
 }
?>
