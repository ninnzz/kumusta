<?php
define('FB_CACHE_LIMIT', 500);
define('FB_POST_LIMIT', 100);
define('FB_SECRET', 'fb_secret');
define('FB_APP_ID', 'fb_app_id');
define('FB_ACCESS_TOKEN', 'fb_access_token');
define('FB_ALBUM', 'fb_timeline_album');
define('FB_PAGE', 'fb_page');
define('FB_POSTS_INTERVAL', 5);
define('FB_TIMELINE_PHOTO', 'fb_timeline_photo');
define('FB_TIMELINE_POST', 'fb_timeline_post');

define('TW_CACHE_LIMIT', 500);
define('TW_API_TIMEOUT', 1);
define('TW_API_MAX_POSTS', 100);
define('TW_API_INTERVAL', 2);
define('TW_KEY', 'tw_key');
define('TW_SECRET', 'tw_secret');
define('TW_OAUTH_TOKEN', 'tw_oauth_token');
define('TW_OAUTH_SECRET', 'tw_oauth_secret');
define('TW_USER_ID', 'tw_user_id');
define('TW_STATUS', 'tw_status');
define('TW_PHOTOS', 'tw_photos');

define("CACHE_MAX_AGE", 120);

define('PARSED_CONTENTS_LIMIT', 100000);
define('PARSE_INTERVAL', 2);
define('CLEANUP_SIZE_INTERVAL', 5);
define('CLEANUP_TIME_INTERVAL', 5);

define("PID_LOCATION", "/home/ubuntu/cheetos/application/sns_feeds/deploy/monit/scripts/sns-cache/");
define("LOG_LOCATION", "/home/ubuntu/cheetos/application/sns_feeds/sns-cache/logs/");

$db = array(
    "hostname" => "localhost",
    "user"     => "root",
    "password" => 'P@ssw0rd',
    "db_name"  => "sns_cache"
);

$dsn = "mysql:dbname=" . $db["db_name"] . ";host=" . $db["hostname"];

function _log($message, $type=NULL) {
    $date = date("Y-m-d");
    $filename = "sns_cache_$date.log";
    error_log($message, 3, LOG_LOCATION . $filename);
}

?>
