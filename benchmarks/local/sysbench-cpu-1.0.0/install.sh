#!/bin/sh

sudo apt-get update
sudo apt-get -y install sysbench
echo \$? > ~/install-exit-status
export cpus=`lscpu|grep ^CPU\(s\)|awk '{print $2}'`
if [ -z "$TITUS_CONFIRM" ]; then
echo "#!/bin/sh
/usr/bin/sysbench --test=cpu --num-threads=$cpus --cpu-max-prime=200000 run > \$LOG_FILE 2>&1
echo \$? > ~/test-exit-status" > sysbench-cpu
chmod +x sysbench-cpu

elif [ $TITUS_CONFIRM == 'true' ]; then
echo "#!/bin/sh
CORES=$TITUS_NUM_CPU
/usr/bin/sysbench --test=cpu --num-threads=\$CORES --cpu-max-prime=200000 run > \$LOG_FILE 2>&1
echo \$? > ~/test-exit-status" > sysbench-cpu
chmod +x sysbench-cpu
fi


