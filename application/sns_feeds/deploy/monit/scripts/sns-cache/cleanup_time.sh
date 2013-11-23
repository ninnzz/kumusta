#!/bin/bash
case $1 in
    start)
        exec 2>&1 php /home/ubuntu/cheetos/application/sns_feeds/sns-cache/scripts/cleanup_time.php &
        ;;
    stop)
        kill `cat /home/ubuntu/cheetos/application/sns_feeds/deploy/monit/scripts/sns-cache/cleanup_time.pid` ;;
    *)
        echo "Usage: cleanup_time {start|stop}" ;;
esac
exit 0
