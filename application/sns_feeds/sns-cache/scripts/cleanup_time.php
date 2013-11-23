<?php

require_once "/home/ubuntu/cheetos/application/sns_feeds/sns-cache/config/config.php";

function cs_log($message) {
    _log("Cleanup by Time: " . $message, 'Clean up by time');
}

$pidFile = PID_LOCATION . "cleanup_time.pid";
$fh = fopen($pidFile, "w") or die("Can't open file");
fwrite($fh, getmypid());
fclose($fh);

set_time_limit(0);

$diff = CACHE_MAX_AGE * 60;
$ext_count = 1;
$pdo = NULL;
while (TRUE) {
    try {
        cs_log(date("Y-m-d H:i:s") . " Deleting rows from cache of age "
            . CACHE_MAX_AGE . " minutes, iteration $ext_count\n");

        // Checking db connection; try 5 times before quitting
        $limit = 5;
        while (!$pdo && $limit > 0) {
            $pdo = new PDO($dsn, $db["user"], $db["password"]);
            $limit--;
            if (!$pdo) {
                sleep(10);
            }
        }
        if ($pdo === NULL) {
            cs_log("ERROR - Unable to connect to database.\n");
            exit();
        }

        // Deleting stuff

        $tables = array("ig_cache" ,"tw_cache" ,"fb_cache" );

        foreach ($tables as $key ) {
            $now = time();
            $sql = "DELETE FROM $key WHERE date_created <= ($now - $diff)";
            $count = $pdo->exec($sql);
            if ($count ) {
                cs_log("Deleted $count rows from $key.\n");
            } else {
                cs_log("Skipping delete.\n");
            }

            cs_log("Deleting rows from $key of age " . CACHE_MAX_AGE
                . " minutes, iteration $ext_count\n");
        }
        

        $now = time();
        $date_diff = ($now - $diff);
        $sql = "DELETE FROM parsed_contents WHERE date_created <= $date_diff";
        $count = $pdo->exec($sql);
        if ($count) {
            cs_log("DELETED $count rows from parsed_contents.\n");
        } else {
            cs_log("Skipping delete.\n");
        }
    } catch (Exception $e) {
        cs_log("ERROR - Connection failed: " . $e->getMessage());
    }

    $pdo = NULL;
    $ext_count++;
    cs_log("Standing by for " . CLEANUP_TIME_INTERVAL . " minute/s.\n");
    sleep(CLEANUP_TIME_INTERVAL*60);
}
$pdo = NULL;

?>
