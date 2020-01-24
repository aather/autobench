#!/bin/sh
sudo apt-get  update
sudo apt-get -y install lmbench
echo \$? > ~/install-exit-status
echo "#!/bin/sh
#sudo numactl --physcpubind=2 --interleave=all /usr/lib/lmbench/bin/x86_64-linux-gnu/lat_mem_rd 2000 64 2>&1|egrep \"^0.00391|^0.12500|^3.00000|^512.00000\"| tr '\n' ' '| awk '{print \$0, \"Memory:\"}' > \$LOG_FILE 2>&1
/usr/lib/lmbench/bin/x86_64-linux-gnu/lat_mem_rd 2000 64 2>&1|egrep \"^0.00391|^0.12500|^3.00000|^512.00000\"| tr '\n' ' '| awk '{print \$0, \"Memory:\"}' > \$LOG_FILE 2>&1
echo \$? > ~/test-exit-status" > lmbench-mem 2>&1
chmod +x lmbench-mem
