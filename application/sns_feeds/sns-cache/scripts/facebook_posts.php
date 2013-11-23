<?php

require_once "/home/ubuntu/cheetos/application/sns_feeds/sns-cache/config/config.php";
require_once "/home/ubuntu/cheetos/application/sns_feeds/sns-cache/libs/facebook.php";

class FB_Posts extends Facebook {

    function __construct($config=NULL) {
        $this->secret = $config["secret"];
        $this->client_id = $config["appId"];
        parent::__construct($config);
    }

    function get_posts($page_id, $limit=FB_POST_LIMIT) {
        $params = array(
            "limit" => $limit,
            "fields" => "id,from,message,picture,link,name,"
                . "caption,properties,icon,type,story,object_id,shares,"
                . "created_time,updated_time,"
                . "comments.limit(1).summary(true),likes.limit(1).summary(true)"
        );
        $result = $this->api("/$page_id/posts", $params);
        return $result;
    }

    function extend_access_token($access_token) {
        $appId = $this->client_id;
        $appSecret = $this->secret;
        $new_access_token = NULL;

        try {
            $graph_url = "https://graph.facebook.com/oauth/access_token?"
                . "client_id={$appId}&client_secret={$appSecret}&"
                . "grant_type=fb_exchange_token&fb_exchange_token={$access_token}";
            $new_access_token = @file_get_contents($graph_url);
            echo $new_access_token;
            $new = array();
            parse_str($new_access_token, $new);
            $new_access_token = $new["access_token"];
            if (!$new_access_token) {
                $new_access_token = $access_token;
            }
        } catch (Exception $e) {
            $new_access_token = $access_token;
        }
        return $new_access_token;
    }

    function get_access_token() {
        $new_access_token = NULL;
        try {
            $url = "https://graph.facebook.com/oauth/access_token?"
                . "client_id={$this->client_id}&client_secret={$this->secret}&"
                . "grant_type=client_credentials";
            $new_access_token = @file_get_contents($url);
            $new = array();
            parse_str($new_access_token, $new);
            $new_access_token = $new["access_token"];
        } catch (Exception $e) {
            $new_access_token = NULL;
        }
        return $new_access_token;
    }

}

function fb_log($message) {
    _log("Facebook API: " . $message);
}

$pidFile = PID_LOCATION . "facebook_posts.pid";
$fh = fopen($pidFile, "w") or die("Can't open file");
fwrite($fh, getmypid());
fclose($fh);

$creds = array();
$pdo;
try {
    $pdo = new PDO($dsn, $db["user"], $db["password"]);
    $sql = "SELECT name, content FROM oauth where name in ('" . FB_SECRET
        . "', '" . FB_APP_ID . "', '" . FB_ACCESS_TOKEN . "')";
    foreach ($pdo->query($sql) as $row) {
        $creds[strtolower($row["name"])] = $row["content"];
    }
} catch (Exception $e) {
    fb_log("Connection failed: " . $e->getMessage());
    exit;
}

$config = array(
    "secret" => $creds[FB_SECRET],
    "appId" => $creds[FB_APP_ID]
);

$access_token = $creds[FB_ACCESS_TOKEN];

$fb = new FB_Posts($config);

while (TRUE) {
    $start_time = time();
    fb_log(date("Y-m-d H:i:s") . " Retrieving posts from timeline.\n");

    $pages = array();
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
            fb_log("ERROR - Unable to connect to database.\n");
            exit();
        }

        $sql = "SELECT user_id, content, name FROM users where "
            . "content_type = '" . FB_PAGE . "'";
        foreach ($pdo->query($sql) as $row) {
            $pages[] = array(
                "user_id"  => $row["user_id"],
                "page_id" => $row["content"],
                "name"     => $row["name"]
            );
        }

       
        $access_token = $fb->get_access_token($access_token);
        $fb->setAccessToken($access_token);
        
        $limit = count($pages);
        for ($i=0; $i < $limit; $i++) {
            try {
                $result = $fb->get_posts($pages[$i]["page_id"]);
                $value = "(" . $pdo->quote($pages[$i]["user_id"]) . ", "
                    . $pdo->quote(json_encode($result["data"])) . ", "
                    . time() . ")";
                $sql = "INSERT INTO fb_cache (user_id, raw_content, date_created) "
                    . "VALUES " . $value;
                $count = $pdo->exec($sql);

                if (!$count) {
                  fb_log(json_encode($pdo->errorInfo()) . "\n");
                }
               	  fb_log("INSERTED $count rows to fb_cache, user: {$pages[$i]["name"]}\n");
            } catch (Exception $e) {
                $result_error = $e->getResult();
                fb_log("Error: " . $e->getMessage(). "\n");
                if (strpos($e->getMessage(), "(#803)") === FALSE) {
                    $access_token = $fb->get_access_token();
                    $fb->setAccessToken($access_token);
                    if($result_error['error']['code'] != 2){
                        $i--;
                    }
                    else{
                        fb_log("Problem with account.\n");
                    }
                    fb_log("Requested new access token.\n");
                }

            }

        }

    } catch (Exception $e) {
        fb_log("Connection failed: " . $e->getMessage());
    }

    $pdo = NULL;

    $exec_time = time() - $start_time;
    $minutes = ($exec_time / 60) % 60;
    $seconds = $exec_time % 60;
    fb_log("Total execution time: $minutes minutes $seconds seconds\n");

    fb_log("Standing by for " . FB_POSTS_INTERVAL . " minute/s.\n");
    sleep(FB_POSTS_INTERVAL*60);
}
$pdo = NULL;

?>
