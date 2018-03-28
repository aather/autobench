EC2_INSTANCE_TYPE="r3.xlarge"
TS=`date +%Y%m%d%H%M`
INS_TYPE=$(echo $EC2_INSTANCE_TYPE| tr "." "\n")
INSTANCE=`echo $INS_TYPE|awk '{b=$1 "-" $2; print b}'`
UNAME=`uname -r`
KERNEL=`echo $UNAME|sed -e 's/\.//g'`
CPUS=`lscpu|grep ^CPU\(|awk '{print $2'}`
# Third argument is the test name: pts/stream-1.3.1
THIRD=$(echo $3| tr "/" "\n")
# we need only test name: stream-1.3.1. Also remove dots, stream-131
MYTEST=`echo $THIRD|awk '{print $2}'|sed -e 's/\.//g'`
if [ $0 = "/usr/bin/phoronix-test-suite-cputests" ] then
  export TEST_RESULTS_NAME="cputests-$MYTEST-$INSTANCE-$KERNEL-$TS"
  export TEST_RESULTS_LATEST="cputests-$MYTEST-$INSTANCE-LATEST"
elif [ $0 = "/usr/bin/phoronix-test-suite-memtests" ] then
  export TEST_RESULTS_NAME="memtests-$MYTEST-$INSTANCE-$KERNEL-$TS"
  export TEST_RESULTS_LATEST="memtests-$MYTEST-$INSTANCE-LATEST"
elif [ $0 = "/usr/bin/phoronix-test-suite-javatests" ] then
  export TEST_RESULTS_NAME="javatests-$MYTEST-$INSTANCE-$KERNEL-$TS"
  export TEST_RESULTS_LATEST="javatests-$MYTEST-$INSTANCE-LATEST"
else
   echo "no test matched"
   exit
fi
export TEST_RESULTS_IDENTIFIER="$EC2_INSTANCE_TYPE-$UNAME"
export TEST_RESULTS_DESCRIPTION="$MYTEST"
export PTS_DIR=/usr/share/phoronix-test-suite
export PTS_MODE="CLIENT"
#export COST_PERF_PER_DOLLAR="$CPUS"
export MONITOR=cpu.usage.summary,memory.usage   
export RESULTS_DIR="/efs/autobench/test-results"

DIR="$RESULTS_DIR/$TEST_RESULTS_NAME"
if [ -d "$DIR" ];
then
 sudo rm -rf $DIR
fi
sudo mkdir $DIR

LDIR="$RESULTS_DIR/$TEST_RESULTS_LATEST"
if [ -d "$LDIR" ];
then
 sudo rm -rf $LDIR
fi
sudo mkdir $LDIR

if [ $PTS_DIR != "`pwd`" ]
then
        cd $PTS_DIR
fi
