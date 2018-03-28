#! /bin/bash

RESULTS="/efs/autobench/test-results"
REPORTS="/efs/autobench/test-reports"
#
# All benchmark results should be dumped into nfs mounted directory 
# autobench is setup to dump into a directory: /efs/autobench/test-results
# If you decide to change the path for dumping test-results, make sure to update following two files:
# 1- /etc/phoronix.xml file, location:
# <ResultsDirectory>/efs/autobench/test-results</ResultsDirectory>
# 2- /etc/autobench_environment
# export RESULTS_DIR="/efs/autobench/test-results"
#
#
# install required packages
# sudo apt-get update
# sudo apt-get -y install nfs-common
# sudo apt-get -y install php-cli php-xml
# sudo apt-get -y install libapache2-mod-php php-mcrypt
# sudo apt-get -y install apache2 libapache2-mod-wsgi
# sudo apt-get -y install php-curl php-sqlite3
# sudo apt-get -y install php-gd
# sudo apt-get -y install g++
# sudo apt-get -y install zip
# sudo apt-get -y install openjdk-8-jdk   # required to run specJVM2008 benchmarks

# setup NFS directories for storing test results and test reports
# sudo mount -t nfs4 -o nfsvers=4.1,rsize=1048576,wsize=1048576,hard,timeo=600,retrans=2 $(curl -s http://169.254.169.254/latest/meta-data/placement/availability-zone).fs-27e02e6e.efs.us-east-1.amazonaws.com: /efs
sudo mkdir -p $RESULTS
sudo mkdir -p $REPORTS

# setup apache server to serve the autobench assets: home page: http:/IP-address/AMIBench/index.php
# sudo cp WEB/* /var/www/html/
# Provide webserver access to test results and reports
# sudo ln -s $RESULTS /var/www/html/RESULTS
# sudo ln -s $REPORTS /var/www/html/REPORTS
# sudo service apache2 restart

# autobench uses open source phoronix Test suite to run benchmarks. Setup phoronix-test-suite.
sudo cp autobench_environment.sh /etc/autobench_environment.sh
sudo cp phoronix-config/phoronix-test-suite.xml /etc/phoronix-test-suite.xml
sudo cp phoronix-config/phoronix-test-suite-cputests /usr/bin/phoronix-test-suite-cputests
sudo cp phoronix-config/phoronix-test-suite-memtests /usr/bin/phoronix-test-suite-memtests
sudo cp phoronix-config/phoronix-test-suite-javatests /usr/bin/phoronix-test-suite-javatests
sudo cp phoronix-config/phoromatic-runtests.service /usr/share/phoromatic-runtests.service
sudo cp phoronix-config/phoronix-runtests /usr/share/phoronix-runtests
sudo cp -r phoronix-config/phoronix-test-suite/ /usr/share/phoronix-test-suite
# sudo cp phoronix-config/phoromatic-client.service /usr/share/phoromatic-client.service
# sudo cp phoronix-config/phoromatic-server.service /usr/share/phoromatic-server.service

# To run SPECjvm2008 java benchmarks, download it from the url: https://www.spec.org/download.html 
# Make sure to download into the same directory where this (setup.sh) script is located
# Uncomment the line below to install SPECjvm2008. It will be installed in root /specJVM2008 directory
# sudo java -jar SPECjvm2008_1_01_setup.jar -i silent   
# install all benchmarks
sudo mkdir -p /var/lib/phoronix-test-suite/test-profiles 
sudo cp -r benchmarks/* /var/lib/phoronix-test-suite/test-profiles/

# copy test reports for demo purposes. 
sudo cp -r sample-test-reports/autobench-reports.tar.gz $REPORTS
sudo gunzip $REPORTS/autobench-reports.tar.gz
sudo tar -xvf $REPORTS/autobench-reports.tar
