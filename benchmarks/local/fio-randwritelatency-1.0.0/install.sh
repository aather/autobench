#!/bin/sh
echo \$? > ~/install-exit-status
export cpus=`lscpu|grep ^CPU\(s\)|awk '{print $2}'`
if [ $cpus -gt 48 ]; then
   cpus=48
fi
echo "#!/bin/sh
echo 3 > /proc/sys/vm/drop_caches
/usr/bin/fio --name=fiotests --ioengine=libaio --rw=randwrite --direct=0 --fsync=20 --size=1g --numjobs=$cpus --iodepth=1 --group_reporting=1 --directory=/mnt --minimal --norandommap --bs=16k --loop=2 | awk -F';' '{print \$81}'|sed 's/^/STATS /' > \$LOG_FILE 2>&1
echo \$? > ~/test-exit-status" > fio-randwritelatency
chmod +x fio-randwritelatency
