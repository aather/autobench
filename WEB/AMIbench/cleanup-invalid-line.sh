#!/bin/bash
# Cleans up the directories with failed and incomplete test results
cd /efs/amibench/results

#sudo find . -type d -empty -delete
pattern="Invalid"
pattern2="AN OUTDATED VERSION"
for i in `ls -d cputest*`
do
        if [ ! -f $i/result.json ]; then 
          echo "removing $i"
          rm -rf $i
       else 
         cat $i/result.json | while read line; 
         do
            if [[ $line =~ $pattern ]] ; then
    	       echo "$line"
               echo "removing $i"
	       rm -rf $i
	    elif [[ $line =~ $pattern2 ]] ; then
    	      echo "$line"
              echo "removing $i"
              rm -rf $i
            fi
          done
       fi
done
for i in `ls -d memtest*`
do
        if [ ! -f $i/result.json ]; then
          echo "removing $i"
          rm -rf $i
       else
         cat $i/result.json | while read line; 
         do
            if [[ $line =~ $pattern ]] ; then
               echo "$line"
               echo "removing $i"
               rm -rf $i
            elif [[ $line =~ $pattern2 ]] ; then
              echo "$line"
              echo "removing $i"
              rm -rf $i
            fi
          done
       fi
done

for i in `ls -d javatest*`
do
        if [ ! -f $i/result.json ]; then
          echo "removing $i"
          rm -rf $i
       else
         cat $i/result.json | while read line; 
         do
            if [[ $line =~ $pattern ]] ; then
               echo "$line"
               echo "removing $i"
               rm -rf $i
            elif [[ $line =~ $pattern2 ]] ; then
              echo "$line"
              echo "removing $i"
              rm -rf $i
            fi
          done
       fi
done
for i in `ls -d s3test*`
do
        if [ ! -f $i/result.json ]; then
          echo "removing $i"
          rm -rf $i
       else
         cat $i/result.json | while read line; 
         do
            if [[ $line =~ $pattern ]] ; then
               echo "$line"
               echo "removing $i"
               rm -rf $i
            elif [[ $line =~ $pattern2 ]] ; then
              echo "$line"
              echo "removing $i"
              rm -rf $i
            fi
          done
       fi
done
