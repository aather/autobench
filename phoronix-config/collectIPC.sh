#!/bin/bash

output=$1

supported=$(perf stat -e cycles -a sleep 1 2>&1 | awk '
    $1 == "<not" { 
        status = "not supported"
        print status
        print "Performance counters are not supported. Skipping IPC measuring ..."}
')

if [ "$supported" == "" ];
then 
    perf stat -e cycles -e cycles \
        -e instructions \
        -I 1000 -a 2>&1 | awk -v hlines=25 -v outf="$output" '
        BEGIN {
            htxt = sprintf(" %5s", "IPC");
            print htxt > outf
            header = hlines
        }
        /invalid/ { print $0 }    # unsupported event
        { gsub(/,/, ""); }
        $3 == "cycles" { cycles = $2; }
        $3 == "instructions" {
            if (--header == 0) {
                print htxt
                header = hlines
            }
            ins = $2
            if (cycles == 0) { cycles = 1 }     # PMCs are broken, or no events
            printf("%5.2f\n", ins / cycles) >> outf
        }
        close(outf)
    '
fi