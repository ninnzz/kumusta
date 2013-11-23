#!/bin/bash
case $1 in
    start)
        exec 2>&1 php /home/ubuntu/cheetos/application/sns_feeds/sns-cache/scripts/facebook_posts.php &
        ;;
    stop)
        kill `cat /home/ubuntu/cheetos/application/sns_feeds/deploy/monit/scripts/sns-cache/facebook_posts.pid` ;;
    *)
        echo "Usage: facebook_posts {start|stop}" ;;
esac
exit 0
