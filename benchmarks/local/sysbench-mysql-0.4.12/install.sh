#!/bin/sh
sudo apt-get update
sudo apt-get -y install sysbench
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'
sudo apt-get -y install mysql-server
export cpus=`lscpu|grep ^CPU\(s\)|awk '{print $2}'`
mysql --user=root --password=root -e "create database test;"
sysbench --test=oltp --oltp-table-size=1000000 --mysql-db=test --mysql-user=root --mysql-password=root prepare

echo \$? > ~/install-exit-status
echo "#!/bin/sh
/usr/bin/sysbench--test=oltp --oltp-table-size=1000000 --mysql-db=test --mysql-user=root --mysql-password=root --max-time=60 --oltp-read-only=on --max-requests=0 --num-threads=$cpus run > \$LOG_FILE 2>&1
echo \$? > ~/test-exit-status" > sysbench-mysql
chmod +x sysbench-mysql
