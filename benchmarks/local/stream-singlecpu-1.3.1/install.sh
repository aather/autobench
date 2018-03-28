#!/bin/sh

tar -jxf stream-2013-01-17.tar.bz2

if [ "X$CFLAGS_OVERRIDE" = "X" ]
then
          CFLAGS="$CFLAGS -O3 -march=native"
else
          CFLAGS="$CFLAGS_OVERRIDE"
fi

#cc stream.c -DSTREAM_ARRAY_SIZE=536870912 -DNTIMES=100 $CFLAGS -fopenmp -o stream-bin
gcc -O2 -mcmodel=medium -DSTREAM_ARRAY_SIZE=536870912 stream.c -o stream-bin
echo \$? > ~/install-exit-status

echo "#!/bin/sh
sudo numactl --physcpubind=2 --interleave=all ./stream-bin > \$LOG_FILE 2>&1
echo \$? > ~/test-exit-status" > stream-singlecpu
chmod +x stream-singlecpu
