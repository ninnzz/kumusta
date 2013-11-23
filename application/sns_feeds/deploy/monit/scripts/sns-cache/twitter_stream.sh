#!/bin/bash
case $1 in
    start)
        exec 2>&1 php /home/ubuntu/cheetos/application/sns_feeds/sns-cache/scripts/twitter_stream.php &
        ;;
    stop)
        kill `cat /home/ubuntu/cheetos/application/sns_feeds/deploy/monit/scripts/sns-cache/twitter_stream.pid` ;;
    *)
        echo "Usage: twitter_stream {start|stop}" ;;
esac
exit 0
