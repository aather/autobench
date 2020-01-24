#!/bin/bash
# Runs all the required scripts to generate a report. 
startime=`date +%s`
echo "Started..."
echo "Cleaning up"
sudo ./cleanup-invalid-line.sh
echo "regress_process_graphs.php"
sudo php ./regress_process_graphs.php
echo "Running prod_cacheresults.php"
sudo php ./prod_cacheresults.php
echo "type_process_graphs.php"
sudo php ./type_process_graphs.php
echo "family_procoess_graphs.php"
sudo php ./family_procoess_graphs.php
echo "Finished..."
endtime=`date +%s`
delta=$(( $endtime - $startime ))

echo "Processing Time is: $delta (seconds), $delta/60 (minutes)"
