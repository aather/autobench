#!/bin/sh
sudo apt-get update
sudo apt-get -y install lmbench
echo \$? > ~/install-exit-status
echo "#!/bin/sh 
/usr/lib/lmbench/bin/x86_64-linux-gnu/mhz| sed 's/^/MHZ /' > \$LOG_FILE 2>&1
echo \$? > ~/test-exit-status" > lmbench-mhz
chmod +x lmbench-mhz
