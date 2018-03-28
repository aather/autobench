#!/bin/sh
#if [ "$1" = "cputests" ] 
#then
# echo "starting cpu perf:"
# echo "sudo perf record -F 99 -a -g -o /mnt/PERF.DATA -- sleep 100000 "
# sudo perf record -F 99 -a -g -o /mnt/PERF.DATA -- sleep 100000 1>/dev/null 2>/dev/null & 
#elif [ "$1" = "storagetests" ]
#then 
#  echo "starting storage perf record"
#  echo "perf record -e block:block_rq_issue -e block:block_rq_complete -a sleep 100000"
#  sudo perf record -o /mnt/PERF.DATA -e block:block_rq_issue -e block:block_rq_complete -a -- sleep 100000 1>/dev/null 2>/dev/null & 
#else
#  echo "perf is not started"
#fi
#sudo /usr/local/bin/osperfrec -o /mnt/osperfrec 5 100000 1>/dev/null 2>/dev/null &
#sudo /efs/amibench/systats.sh &

