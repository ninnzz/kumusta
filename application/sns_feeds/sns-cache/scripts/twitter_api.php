<?php

require_once "/home/ubuntu/cheetos/application/sns_feeds/sns-cache/config/config.php";
require_once "/home/ubuntu/cheetos/application/sns_feeds/sns-cache/libs/twitteroauth-master/twitteroauth/twitteroauth.php";

function tat_log($message) {
    _log("Twitter API: " . $message);
}

$pidFile = PID_LOCATION . "twitter_api.pid";
$fh = fopen($pidFile, "w") or die("Can't open file");
fwrite($fh, getmypid());
fclose($fh);

set_time_limit(0);

$creds = array();
$pdo;
try {
    $pdo = new PDO($dsn, $db["user"], $db["password"]);
    $sql = "SELECT name, content FROM oauth WHERE name in ('" . TW_KEY . "', '"
        . TW_SECRET . "', '" . TW_OAUTH_TOKEN . "', '" . TW_OAUTH_SECRET . "')";
    foreach ($pdo->query($sql) as $row) {
        $creds[strtolower($row["name"])] = $row["content"];
    }
} catch (Exception $e) {
    tat_log("Connection failed: " . $e->getMessage());
    exit;
}

$to = new TwitterOauth($creds[TW_KEY], $creds[TW_SECRET],
    $creds[TW_OAUTH_TOKEN], $creds[TW_OAUTH_SECRET]);
$to->timeout = TW_API_TIMEOUT * 60;
$to->connecttimeout = TW_API_TIMEOUT * 60;

$params = array(
    "include_rts" => 1,
    "count"       => TW_API_MAX_POSTS
);

$ext_count = 1;
$rate_limit = 0;
while (TRUE) {
    $start_time = time();
    tat_log(date("Y-m-d H:i:s") . " Retrieving user timeline: $ext_count\n");

    try {
        $l = 5;
        while (!$pdo && $l > 0) {
            $pdo = new PDO($dsn, $db["user"], $db["password"]);
            $l--;
            if (!$pdo) {
                sleep(10);
            }
        }
        if ($pdo === NULL) {
            tp_log("ERROR - Unable to connect to database.\n");
            exit();
        }

        $follows = array();
        $users = "SELECT user_id, name, content FROM users WHERE content_type = '"
            . TW_USER_ID . "'";
        foreach ($pdo->query($users) as $row) {
            $follows[] = $row;
        }

        $stopped = FALSE;
        $cfollows = count($follows);
        for ($i=0; $i < $cfollows; $i++) {
            $follow = $follows[$i];

            if ($rate_limit <= 0) {
                while ($rate_limit <= 0) {
                    $limit = $to->get("application/rate_limit_status", array("statuses"));
                    $statuses = json_decode(json_encode($limit->resources->statuses), TRUE);
                    $rate = $statuses["/statuses/user_timeline"];
                    $rate_limit = $rate["remaining"];

                    tat_log("*** " . date("Y-m-d H:i:s") . " REMAINING REQUESTS: "
                        . $rate["remaining"] . " ***\n");
                    $wait = 0;
                    if ($rate["remaining"] <= 0) {
                        $wait = ($rate["reset"] - time()) + 5;
                        $minutes = ($wait/60) % 60;
                        $seconds = $wait % 60;
                        tat_log("Request limit reset at " . date("Y-m-d H:i:s", $rate["reset"])
                            . ", time now " . date("Y-m-d H:i:s") . "\n");
                        tat_log("Waiting for limit to reset: " . $minutes . "mins " . $seconds . "s\n");
                        if ($minutes > 3) $stopped = TRUE;
                        if ($wait > 0) sleep($wait);
                    }
                    if ($wait < 0) sleep(10);
                }
            }

            $params["user_id"] = $follow["content"];
            $response = $to->get("statuses/user_timeline", $params);
            $values = array();
            if (isset($response->errors)) {
                $error = json_encode($response->errors);

                if ($error == '[{"message":"Rate limit exceeded","code":88}]') {
                    $rate_limit = 0;
                }
                
                if ($error == '[{"message":"Sorry, that page does not exist","code":34}]') {
                    $i++;   
                }
                else{
                    $i--;
                }

                tat_log("Error: " . json_encode($response->errors) . "\n");
            } else {
                $rate_limit--;
                $value = "(" . $follow["user_id"] .
                    ", " . $pdo->quote(json_encode($response)) . ", "
                    . time() . ")";

                $sql = "INSERT INTO tw_cache (user_id, raw_content, date_created) "
                    . "VALUES " . $value;
                $count = $pdo->exec($sql);
                if (!$count) {
                    tat_log(json_encode($pdo->errorInfo()) . "\n");
                } else {
                    tat_log("INSERTED " . $count . " rows to tw_cache, user: {$follow["name"]}\n");
                }
            }
        }
    } catch (Exception $e) {
        $pdo = NULL;
        tp_log("Connection failed: " . $e->getMessage());
        exit;
    }

    $pdo = NULL;
    $exec_time = time()-$start_time;
    $minutes = ($exec_time/60) % 60;
    $seconds = $exec_time % 60;
    tat_log("Total execution time: " . $minutes . " minutes " . $seconds . " seconds.\n");

    $ext_count++;
    if (!$stopped) {
        tat_log("Standing by for " . TW_API_INTERVAL . " minute/s.\n");
        sleep(TW_API_INTERVAL*60);
    }
}
$pdo = NULL;

?>
