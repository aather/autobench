#!/bin/sh
sudo apt-get update
sudo apt-get -y  install lmbench
echo \$? > ~/install-exit-status
echo "#!/bin/sh
sudo numactl --physcpubind=2 --interleave=all /usr/lib/lmbench/bin/x86_64-linux-gnu/bw_mem 2500m cp 2>&1|awk '{print \$0, \"Memory:\"}'> \$LOG_FILE 2>&1
echo \$? > ~/test-exit-status" > lmbench-bw 2>&1
chmod +x lmbench-bw
