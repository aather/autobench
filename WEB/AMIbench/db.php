<?php
 try
  {
    //open the database
    $db = new PDO('sqlite:AMIbench.sqlite');

    //create the database
    $db->exec("CREATE TABLE Instype (id INTEGER PRIMARY KEY, name TEXT, model TEXT, vcpus INTEGER, memory INTEGER, storage INTEGER, network TEXT, eni INTEGER, nvme INTEGER, sriov INTEGER, xennet INTEGER, ebsopt TEXT, numa INTEGER )");    

    //insert some data...
    $db->exec( 
    // r3
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 			      
            VALUES ('r3-xlarge','IvyBridge-E5-2670v2',4,30.5,80,700,0,0,1,0,'No:62.5:4000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('r3-2xlarge','IvyBridge-E5-2670v2',8,61,160,960,0,0,1,0,'No:125:8000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('r3-4xlarge','IvyBridge-E5-2670v2',16,122,320,2000,0,0,1,0,'No:250:16000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('r3-8xlarge','IvyBridge-E5-2670v2',32,244,640,5000,0,0,1,0,'No:NA:NA',1);" .
    // i2
    "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa)                          
            VALUES ('i2-xlarge','IvyBridge-E5-2670v2',4,30.5,800,700,0,0,1,0,'No:62.5:4000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('i2-2xlarge','IvyBridge-E5-2670v2',8,61,1600,960,0,0,1,0,'No:125:8000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('i2-4xlarge','IvyBridge-E5-2670v2',16,122,3200,2000,0,0,1,0,'No:250:16000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('i2-8xlarge','IvyBridge-E5-2670v2',32,244,6400,9400,0,0,1,0,'No:NA:NA',1);". 
     // d2
    "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa)                         
            VALUES ('d2-xlarge','Haswell-E5-2676v3',4,30.5,6000,700,0,0,1,0,'Yes:93.75:6000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('d2-2xlarge','Haswell-E5-2676v3',8,61,12000,1000,0,0,1,0,'Yes:125:8000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('d2-4xlarge','Haswell-E5-2676v3',16,122,24000,2000,0,0,1,0,'Yes:250:16000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('d2-8xlarge','Haswell-E5-2676v3',32,244,48000,9400,0,0,1,0,'Yes:500:32000',1);" .
     // c4
     "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa)                         
            VALUES ('c4-xlarge','Haswell-E5-2666v3',4,7.5,0,1000,0,0,1,0,'Yes:93.5:6000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('c4-2xlarge','Haswell-E5-2666v3',8,15,0,1000,0,0,1,0,'Yes:125:8000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('c4-4xlarge','Haswell-E5-2666v3',16,30,0,2000,0,0,1,0,'Yes:250:16000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('c4-8xlarge','Haswell-E5-2666v3',36,60,0,9400,0,0,1,0,'Yes:500:32000',1);" .
    //r4
    "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa)                         
            VALUES ('r4-xlarge','Broadwell-E5-2686v4',4,30.5,0,'1000:9000',1,0,0,0,'Yes:106.25:6000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('r4-2xlarge','Broadwell-E5-2686v4',8,61,0,'2000:9000',1,0,0,0,'Yes:212.25:12000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('r4-4xlarge','Broadwell-E5-2686v4',16,122,0,'4000:9000',1,0,0,0,'Yes:437.5:18750',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('r4-8xlarge','Broadwell-E5-2686v4',32,244,0,9400,1,0,0,0,'Yes:875:37500',1);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('r4-16xlarge','Broadwell-E5-2686v4',64,488,0,23000,1,0,0,0,'Yes:1750:75000',1);" .
    //m4 
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa)                         
	    VALUES ('m4-xlarge','Broadwell-E5-2686v4-Haswell-E5-2676v3',4,16,0,700,0,0,1,0,'Yes:93.75:6000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('m4-2xlarge','Broadwell-E5-2686v4-Haswell-E5-2676v3',8,32,0,1000,0,0,1,0,'Yes:125:8000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('m4-4xlarge','Broadwell-E5-2686v4-Haswell-E5-2676v3',16,64,0,2000,0,0,1,0,'Yes:250:16000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('m4-16xlarge','Broadwell-E5-2686v4-Haswell-E5-2676v3',64,256,0,23000,0,0,1,0,'Yes:1250:65000',1);" .

     //h1
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('h1-2xlarge','Broadwell-E5-2686v4',8,32,2048,'1000:7000',1,0,0,0,'Yes:218.75:12000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('h1-4xlarge','Broadwell-E5-2686v4',16,64,4096,'2000:9000',1,0,0,0,'Yes:437.5:20000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('h1-8xlarge','Broadwell-E5-2686v4',32,128,8192,9400,1,0,0,0,'Yes:875:40000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('h1-16xlarge','Broadwell-E5-2686v4',64,256,16384,9400,1,0,0,0,'Yes:1750:80000',1);" .
     //t2
    "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa)                         
            VALUES ('t2-xlarge','Generic',4,16,0,700,0,0,0,1,'No',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('t2-2xlarge','Generic',8,32,0,960,0,0,0,1,'No',0);" .
     //x1
     "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa)                         
	    VALUES ('x1-16xlarge','Haswell-E7-8880v3',64,976,1920,9400,1,0,0,0,'Yes:875:40000',1);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('x1-32xlarge','Haswell-E7-8880v3',128,1952,3840,23000,1,0,0,0,'Yes:1750:80000',1);" .
    //x1e
     "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa)                         
            VALUES ('x1e-xlarge','Haswell-E7-8880v3',4,122,120,'600:6144',1,0,0,0,'Yes:62.5:3700',0);" . 
     "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa)                         
            VALUES ('x1e-2xlarge','Haswell-E7-8880v3',8,244,240,'1000:7168',1,0,0,0,'Yes:125:7400',0);" .
     "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa)                         
            VALUES ('x1e-4xlarge','Haswell-E7-8880v3',16,488,480,'2000:9000',1,0,0,0,'Yes:218.75:10000',0);" .
     "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa)                         
            VALUES ('x1e-8xlarge','Haswell-E7-8880v3',32,976,960,9400,1,0,0,0,'Yes:437.5:20000',0);" .
     "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa)                         
            VALUES ('x1e-16xlarge','Haswell-E7-8880v3',64,1952,1920,9400,1,0,0,0,'Yes:875:40000',1);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('x1e-32xlarge','Haswell-E7-8880v3',128,3904,3840,23000,1,0,0,0,'Yes:1750:80000',1);" .
 
    //m5 
    "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa)                         
            VALUES ('m5-xlarge','Skylake-Platinum-8175',4,16,0,'1000:9000',0,0,1,0,'Yes:265:16000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('m5-2xlarge','Skylake-Platinum-8175',8,32,0,'2000:9000',0,0,1,0,'Yes:265:16000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('m5-4xlarge','Skylake-Platinum-8175',16,64,0,'4000:9000',0,0,1,0,'Yes:265:16000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('m5-12xlarge','Skylake-Platinum-8175',48,192,0,9400,0,0,1,0,'Yes:625:32500',1);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('m5-24xlarge','Skylake-Platinum-8175',96,384,0,23000,0,0,1,0,'Yes:1250:65000',1);" .

   //c5 
    "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa)                         
            VALUES ('c5-xlarge','Skylake-Platinum',4,8,0,'1000:9000',1,0,0,0,'Yes:281:6000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('c5-2xlarge','Skylake-Platinum',8,16,0,'2000:9000',1,0,0,0,'Yes:281:8000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('c5-4xlarge','Skylake-Platinum',16,32,0,'4000:9000',1,0,0,0,'Yes:281:16000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('c5-9xlarge','Skylake-Platinum',36,72,0,9400,1,0,0,0,'Yes:563:65000',1);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('c5-18xlarge','Skylake-Platinum',72,144,0,23000,1,0,0,0,'Yes:1125:65000',1);" .

      //i3
    "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa)                         
            VALUES ('i3-xlarge','Broadwell-E5-2686v4',4,30.5,972,'1000:9000',1,1,0,0,'Yes:106.25:6000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('i3-2xlarge','Broadwell-E5-2686v4',8,61,1945,'2000:9000',1,1,0,0,'Yes:212.5:12000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('i3-4xlarge','Broadwell-E5-2686v4',16,122,3891,'4000:9000',1,1,0,0,'Yes:437.5:16000',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('i3-8xlarge','Broadwell-E5-2686v4',32,244,7782,10000,9400,1,0,0,'Yes:875:32500',0);" .
   "INSERT INTO Instype (name,model,vcpus,memory,storage,network,eni,nvme,sriov,xennet,ebsopt,numa) 
            VALUES ('i3-16xlarge','Broadwell-E5-2686v4',64,488,15564,23000,1,1,0,0,'Yes:1750:65000',1);" 
	);

    //now output the data to a simple html table...
    print "<table border=1>";
    print "<th><td>InstType</td><td>Model</td><td>vCPU</td><td>Memory</td><td>Storage</td><td>Network</td><td>Price</td><td>eni</td><td>sriov</td><td>xennet</td><td>ebs Optimized</td><td>numa</td></th>";
    $result = $db->query('SELECT * FROM Instype');
    foreach($result as $row)
    {
      print "<tr><td>".$row['name']."</td>"; print "\n";
      print "<td>".$row['model']."</td>"; print "\n";
      print "<td>".$row['vcpus']."</td>"; print "\n";
      print "<td>".$row['memory']."</td></tr>"; print "\n";
      print "<td>".$row['storage']."</td></tr>"; print "\n";
      print "<td>".$row['network']."</td></tr>"; print "\n";
      print "<td>".$row['eni']."</td></tr>"; print "\n";
      print "<td>".$row['nvme']."</td></tr>"; print "\n";
      print "<td>".$row['sriov']."</td></tr>"; print "\n";
      print "<td>".$row['xennet']."</td></tr>"; print "\n";
      print "<td>".$row['ebsopt']."</td></tr>"; print "\n";
      print "<td>".$row['numa']."</td></tr>"; print "\n";
    }
    print "</table>";

    // close the database connection
    $db = NULL;
  }
  catch(PDOException $e)
  {
    print 'Exception : '.$e->getMessage();
  }
?>
