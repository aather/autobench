#!/bin/sh

sudo apt-get update
sudo apt-get -y install sysbench
echo \$? > ~/install-exit-status
export cpus=`lscpu|grep ^CPU\(s\)|awk '{print $2}'`
export mem=`free -g|grep ^Mem:|awk '{print $7}'`
if [ -z "$TITUS_CONFIRM" ]; then
echo "#!/bin/sh
/usr/bin/sysbench --test=memory --memory-block-size=1M --memory-total-size=${mem}G --threads=$cpus  run > \$LOG_FILE 2>&1
echo \$? > ~/test-exit-status" > sysbench-mem
chmod +x sysbench-mem

elif [ $TITUS_CONFIRM == 'true' ]; then
echo "#!/bin/sh
CORES=$TITUS_NUM_CPU
/usr/bin/sysbench --test=memory --memory-block-size=1M --memory-total-size=${mem}G --threads=\$CORES  run > \$LOG_FILE 2>&1
echo \$? > ~/test-exit-status" > sysbench-mem
chmod +x sysbench-mem
fi
