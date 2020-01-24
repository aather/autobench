#!/bin/sh
echo \$? > ~/install-exit-status
export cpus=`lscpu|grep ^CPU\(s\)|awk '{print $2}'`
if [ $cpus -gt 16 ]; then
   cpus=16
fi
echo "#!/bin/sh
echo 3 > /proc/sys/vm/drop_caches
/usr/bin/fio --name=fiotests --ioengine=libaio --rw=read --direct=0 --size=1g --numjobs=$cpus --iodepth=1 --group_reporting=1 --directory=/mnt --minimal --fadvise_hint=1 --bs=128k | awk -F';' '{print \$7/1024}'|sed 's/^/STATS /' > \$LOG_FILE 2>&1
echo \$? > ~/test-exit-status" > fio-seqread
chmod +x fio-seqread
