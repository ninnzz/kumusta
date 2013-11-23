#!/bin/bash
case $1 in
    start)
        exec 2>&1 php /home/ubuntu/cheetos/application/sns_feeds/sns-cache/scripts/parse_cache.php &
        ;;
    stop)
        kill `cat /home/ubuntu/cheetos/application/sns_feeds/deploy/monit/scripts/sns-cache/parse_cache.pid` ;;
    *)
        echo "Usage: parse_cache {start|stop}" ;;
esac
exit 0
