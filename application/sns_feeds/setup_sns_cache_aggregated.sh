#!/bin/bash
PWD=`pwd`
STDIR="$(dirname "$PWD")"
CONFIGDIR="dev"
#WEBDIR="/var/www/api"

WEBDIR="/home/ubuntu/cheetos/application/sns_feeds/sns-cache/"

echo "Setting up SNS Cache."
echo " - Checking file: sns-cache/config/config.php"
FILE=$WEBDIR/config/config.php
if [ ! -f $FILE ]; then
    echo "Config file sns-cache/config/config.php does not exist. Please make one and run this script again."
    exit 0;
fi

echo "Creating SNS Cache database."
mysql -uroot -pP@ssw0rd < $STDIR/sns_feeds/sns-cache/db/sns_cache_db.sql;

echo " - Creating tables.";

php $STDIR/sns_feeds/sns-cache/db/create_tables.php;

#echo " - Adding Users.";

#php $STDIR/sns_feeds/sns-cache/db/add_users.php;



echo "Done.";
