#!/bin/bash

while true    
do
   ATIME=`stat -c %Z shopello.less`
   BTIME=`stat -c %Z shopello_admin.less`

   if [ "$ATIME" != "$LTIME" ] || [ "$BTIME" != $"$KTIME" ]
   then    
       bash less.sh
       LTIME=$ATIME
       KTIME=$BTIME
   fi
   sleep 2
done
