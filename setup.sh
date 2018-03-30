#! /bin/bash

DIR=$PWD
RESULTS="/efs/autobench/test-results"
WEBDIR="/var/www/html"
REPORTS="$WEBDIR/AMIbench/test-reports"
#---------------------------------
# All benchmark results should be dumped into shared NFS mounted directory 
# autobench is setup to dump benchmark results into a dir: /efs/autobench/test-results
# If you decide to change the above directory path, update two files belows:
# /etc/phoronix.xml file
# Look for pattern: <ResultsDirectory>/efs/autobench/test-results</ResultsDirectory>
# /etc/autobench_environment.sh
# Look for pattern: export RESULTS_DIR="/efs/autobench/test-results"
#--------------------------------
#
# If not running in AWS cloud, then update the file: /etc/autobench_environment.sh
# In the file uncomment line: #EC2_INSTANCE_TYPE="r3.xlarge" 
# In the filecomment out line: EC2_INSTANCE_TYPE=`curl -s http://169.254.169.254/latest/meta-data/instance-id`
#
# install required packages
sudo apt-get update
sudo apt-get -y install nfs-common
sudo apt-get -y install php-cli php-xml
sudo apt-get -y install libapache2-mod-php php-mcrypt
sudo apt-get -y install apache2 libapache2-mod-wsgi
sudo apt-get -y install php-curl php-sqlite3
sudo apt-get -y install php-gd
sudo apt-get -y install g++
sudo apt-get -y install zip
# required for specJVM2008 benchmarks
sudo apt-get -y install openjdk-8-jdk 

# If you have NFS server configured, then mount it under /efs directory to avoid updating additional files 
# setup NFS directories for storing test results
# sudo mount -t nfs4 -o nfsvers=4.1,rsize=1048576,wsize=1048576,hard,timeo=600,retrans=2 $(curl -s http://169.254.169.254/latest/meta-data/placement/availability-zone).fs-xxxxxx.efs.us-east-1.amazonaws.com: /efs
# For demo, we will just create a directory and dump results in it.
sudo mkdir -p $RESULTS

# setup apache server to serve the autobench reports: homepage: http:/IP-address/AMIBench/index.php
sudo cp -r WEB/* $WEBDIR
# copy demo test reports in web directory.
sudo mkdir -p $REPORTS
sudo cp -r sample-test-reports/autobench-reports.tar.gz $REPORTS 
cd $REPORTS
sudo gunzip autobench-reports.tar.gz
sudo tar -xf autobench-reports.tar
cd $WEBDIR/AMIbench
# create and papulate SQLite db file AMIbench.sqlite into web directory
sudo php ./db.php
cd $DIR
# setup path for webserver for test results and test reports
sudo ln -s $RESULTS $WEBDIR/RESULTS
sudo ln -s $REPORTS $WEBDIR/REPORTS
sudo service apache2 restart

# autobench uses open source phoronix Test suite to run benchmarks. Setup phoronix-test-suite.
sudo cp autobench_environment.sh /etc/autobench_environment.sh
sudo cp phoronix-config/phoronix-test-suite.xml /etc/phoronix-test-suite.xml
sudo cp phoronix-config/phoronix-test-suite-cputests /usr/bin/phoronix-test-suite-cputests
sudo ln -s /usr/bin/phoronix-test-suite-cputests /usr/bin/phoronix-test-suite-memtests
sudo ln -s /usr/bin/phoronix-test-suite-cputests /usr/bin/phoronix-test-suite-javatests
sudo cp phoronix-config/phoromatic-runtests.service /usr/share/phoromatic-runtests.service
sudo cp phoronix-config/phoronix-runtests /usr/share/phoronix-runtests
sudo cp -r phoronix-config/phoronix-test-suite/ /usr/share/phoronix-test-suite

# To run SPECjvm2008 java benchmarks, download it from the url: https://www.spec.org/download.html 
# Make sure to download into the same directory where this (setup.sh) script is located
# Run the command below to install SPECjvm2008. It will be installed in root /specJVM2008 directory
# sudo java -jar SPECjvm2008_1_01_setup.jar -i silent   
#
# install all benchmarks
cd $DIR
sudo mkdir -p /var/lib/phoronix-test-suite/test-profiles 
sudo cp -r benchmarks/* /var/lib/phoronix-test-suite/test-profiles/
