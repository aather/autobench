#!/bin/sh
echo \$? > ~/install-exit-status
export cpus=`lscpu|grep ^CPU\(s\)|awk '{print $2}'`
if [ $cpus -gt 48 ]; then
   cpus=48
fi
echo "#!/bin/sh
echo 3 > /proc/sys/vm/drop_caches
/usr/bin/fio --name=fiotests --ioengine=libaio --rw=randrw --direct=0 --size=1g --numjobs=$cpus --iodepth=1 --group_reporting=1 --unified_rw_reporting=1 --rwmixread=80 --rwmixwrite=20 --directory=/mnt --minimal --fsync=20 --bs=16k --loop=2 | awk -F';' '{print \$8}'|sed 's/^/STATS /' > \$LOG_FILE 2>&1
echo \$? > ~/test-exit-status" > fio-randmixed
chmod +x fio-randmixed
