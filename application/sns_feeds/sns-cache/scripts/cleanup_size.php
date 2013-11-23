<?php

require_once "/home/ubuntu/cheetos/application/sns_feeds/sns-cache/config/config.php";


function cs_log($message) {
    _log("Cleanup by Size: " . $message);
}

$pidFile = PID_LOCATION . "cleanup_size.pid";
$fh = fopen($pidFile, "w") or die("Can't open file");
fwrite($fh, getmypid());
fclose($fh);

set_time_limit(0);

$ext_count = 1;

$pdo = NULL;
while (TRUE) {
    cs_log(date("Y-m-d H:i:s") . " Deleting oldest rows to meet table limits "
        . "iteration $ext_count\n");

    try {
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

        $tables = array(
            "ig_cache" => IG_CACHE_LIMIT,
            "tw_cache" => TW_CACHE_LIMIT,
            "fb_cache" => FB_CACHE_LIMIT,
            "parsed_contents" => PARSED_CONTENTS_LIMIT
        );

        foreach ($tables as $table => $table_limit) {
            $sql = "SELECT COUNT(*) FROM $table";
            $count = 0;
            foreach ($pdo->query($sql) as $row) {
                $count = $row[0];
            }

            if ($count > $table_limit) {
                cs_log("Table $table size: $count, trimming table $table.\n");
                
                $id = "SELECT MAX(id) FROM $table";
                foreach ($pdo->query($id) as $row) {
                    $max_id = $row[0];
                }
                $limit = $max_id - $table_limit;
                $sql = "DELETE FROM $table WHERE id <= $limit";
                $count = $pdo->exec($sql);
                cs_log("DELETED $count \n");
                cs_log("MAX ID $sql\n");
            } else {
                cs_log("Table $table size: $count, less thank limit $table_limit,"
                    . " skipping delete.\n");
            }

        }
    } catch (Exception $e) {
        cs_log("ERROR - Connection failed: " . $e->getMessage());
    }

    $ext_count++;
    $pdo = NULL;
    cs_log("Stading by for " . CLEANUP_SIZE_INTERVAL . " minute/s.\n");
    sleep (CLEANUP_SIZE_INTERVAL*60);
}
$pdo = NULL;

?>
