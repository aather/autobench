#!/bin/sh
java -jar /efs/SPECJVM2008/SPECjvm2008_1_01_setup.jar -i silent
echo \$? > ~/install-exit-status
echo "#!/bin/sh
cd /SPECjvm2008
java -XX:+PreserveFramePointer -jar SPECjvm2008.jar -ikv -wt 5s -it 60s derby > \$LOG_FILE 2>&1
echo \$? > ~/test-exit-status" > java-derby 
chmod +x java-derby
