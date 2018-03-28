#!/bin/sh

tar -zxvf llcbench-20170104.tar.gz
cd llcbench/

make linux-mpich
make cache-bench
echo $? > ~/install-exit-status

cd ..

echo "#!/bin/sh
cd llcbench/cachebench/
sudo numactl --physcpubind=2 --interleave=all ./cachebench \$@ > \$LOG_FILE" > cachebench
chmod +x cachebench
