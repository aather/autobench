#!/bin/sh

sudo apt-get update
sudo apt-get -y install sysbench
echo \$? > ~/install-exit-status
export cpus=`lscpu|grep ^CPU\(s\)|awk '{print $2}'`
echo "#!/bin/sh
/usr/bin/sysbench --test=memory --num-threads=$cpus run > \$LOG_FILE 2>&1
echo \$? > ~/test-exit-status" > sysbench-mem
chmod +x sysbench-mem
