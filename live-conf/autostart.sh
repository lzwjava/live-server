#!/bin/sh
echo "monitoring srs..."
while true; do 
    count=`ps -ef | grep srs | grep -v grep`
    if [ "$?" != "0" ]; then
      echo "start srs..."
      ./objs/srs -c conf/live.conf
    else
      now=$(date +"%T")
	#echo "$now srs runing..."
    fi
    sleep 1; 
done
