<?php

require_once "/home/ubuntu/cheetos/application/sns_feeds/sns-cache/config/config.php";

function pc_log($message) {
    _log("Parse Cache: " . $message);
}

$pidFile = PID_LOCATION . "parse_cache.pid";
$fh = fopen($pidFile, "w") or die("Can't open file");
fwrite($fh, getmypid());
fclose($fh);

function parse_fb($user_id, $data) {
    $parsed_posts = array();
    foreach ($data as $d) {
        $parsed = array();

        if($d["type"] === "photo"){$type = FB_TIMELINE_PHOTO;}
        else{$type = FB_TIMELINE_POST;}

        $parsed["user_id"] = $user_id;
        $parsed["content_type"] = $type;
        $parsed["post_id"] = $d["id"];

        if (isset($d["shares"])) {
            $d["shares"] = $d["shares"];
        }
        if (isset($d["likes"])) {
            $d["likes"] = $d["likes"]["summary"]; 
        }

        if (isset($d["comments"])) {
            unset($d["comments"]["summary"]["order"]);
            $d["comments"] = $d["comments"]["summary"];
        }
        $d["sns_content_type"] = $type;
        
        $parsed["parsed_content"] = json_encode($d);
        $parsed["post_date"] = strtotime($d["created_time"]);

        $parsed_posts[] = $parsed;
    }
    return $parsed_posts;
}

function parse_tw($user_id, $data) {
    $parsed_tweets = array();
    foreach ($data as $d) {
        $post = array();
        $parsed = array();

        if(isset($d["entities"]["media"])){$type = TW_PHOTOS;}
        else{$type = TW_STATUS;}

        $parsed["user_id"] = $user_id;
        $parsed["content_type"] = $type;
        $parsed["post_id"] = $d["id"];

        $post["id"] = $d["id"];
        $post["created_at"] = $d["created_at"];
        $post["text"] = $d["text"];
        $post["source"] = $d["source"];
        $post["truncated"] = $d["truncated"];
        $post["geo"] = $d["geo"];
        $post["coordinates"] = $d["coordinates"];
        $post["retweet_count"] = $d["retweet_count"];
        $post["favorite_count"] = $d["favorite_count"];
        $post["place"] = $d["place"];
        $post["entities"] = $d["entities"];

        $post["sns_content_type"] = $type;

        $parsed["parsed_content"] = json_encode($post);
        $parsed["post_date"] = strtotime($d["created_at"]);

        $parsed_tweets[] = $parsed;
    }
    return $parsed_tweets;
}

function parse_ig($user_id, $data) {
    $parsed_photos = array();
    foreach ($data as $d) {
        $post = array();
        $parsed = array();

        $parsed["user_id"] = $user_id;
        $parsed["content_type"] = IG_PHOTO;
        $parsed["post_id"] = $d["id"];

        $post["id"] = $d["id"];
        $post["attribution"] = $d["attribution"];
        $post["tags"] = $d["tags"];
        $post["type"] = $d["type"];
        $post["location"] = $d["location"];
        if(isset($d["comments"])){
            $post["comments"] = $d["comments"];
        }
        $post["filter"] = $d["filter"];
        $post["created_time"] = $d["created_time"];
        $post["link"] = $d["link"];
        if(isset($d["likes"])){
            $post["likes"] = $d["likes"];
        }
        $post["images"] = $d["images"];
        $post["users_in_photo"] = $d["users_in_photo"];
        $post["caption"] = $d["caption"];
        $post["sns_content_type"] = IG_PHOTO;

        $parsed["parsed_content"] = json_encode($post);
        $parsed["post_date"] = $d["created_time"];

        $parsed_photos[] = $parsed;
    }
    return $parsed_photos;
}



$pdo = NULL;
$cache = array("ig_cache", "tw_cache", "fb_cache");
$count = 0;
$no_cached = 0;
while (TRUE) {
    try {
        $limit = 5;
        while (!$pdo && $limit > 0) {
            $pdo = new PDO($dsn, $db["user"], $db["password"]);
            $limit--;
            if (!$pdo) {
                sleep(10);
            }
        }
        if (!$pdo) {
            pc_log("ERROR - Unable to connect to database.\n");
            exit();
        }

        $sql = "SELECT id, user_id, raw_content, min(date_created) from "
            . $cache[$count];
        $response = NULL;
        $id = 0;
        $user_id = 0;
        foreach ($pdo->query($sql) as $row) {
            $id = $row["id"];
            $response = json_decode($row["raw_content"], TRUE);
            $user_id = $row["user_id"];
        }

        if ($id > 0) {
            $parsed_content = array();
            switch ($count) {
                case 0:
                    $parsed_content = parse_ig($user_id, $response);
                    break;
                case 1: 
                    $parsed_content = parse_tw($user_id, $response);
                    break;
                case 2:
                    $parsed_content = parse_fb($user_id, $response);
            }
            $values = array();
            foreach ($parsed_content as $content) {
                $v = "(" . $content["user_id"] . ", "
                    . $pdo->quote($content["content_type"]) . ", "
                    . $pdo->quote($content["post_id"]) . ", "
                    . $pdo->quote($content["parsed_content"]) . ", "
                    . $content["post_date"] . ", " . time() . ")";
                $values[] = $v;
            }
            $value = implode(",", $values);

            $insert_count = -1;
            if (count($values) > 0) {
                $sql = "INSERT INTO parsed_contents (user_id, content_type, "
                    . "post_id, parsed_content, post_date, date_created) "
                    . "VALUES " . $value;
                $insert_count = $pdo->exec($sql);
            }

            $delete_count = 0;
            if ($insert_count > 0 || $insert_count == -1) {
                $sql = "DELETE FROM " . $cache[$count] . " WHERE id = $id";
                $delete_count = $pdo->exec($sql);
            }

            pc_log(date("Y-m-d H:i:s") . " Inserted $insert_count, "
                . "Deleted $delete_count, table {$cache[$count]}\n");
            if (!$delete_count) {
                pc_log("Error: " . json_encode($pdo->errorInfo()) . "\n");
            }
        } else {
            if ($no_cached == 2) {
                $pdo = NULL;
                pc_log(date("Y-m-d H:i:s") . " No cached contents retrieved.\n");
                sleep(10);
            }
        }
        $count = $count == 2 ? 0 : $count+1;
        $no_cached = $count;
    } catch (Exception $e) {
        $pdo = NULL;
        pc_log("Connection failed: " . $e->getMessage());
        exit();
    }

    sleep(PARSE_INTERVAL);
}
$pdo = NULL;

?>
