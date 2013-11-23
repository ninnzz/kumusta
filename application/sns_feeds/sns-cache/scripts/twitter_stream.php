<?php
$dir = "/home/ubuntu/cheetos/application/sns_feeds/sns-cache/";
require_once $dir."config/config.php";
require_once $dir."libs/twitteroauth-master/twitteroauth/twitteroauth.php";
require_once $dir."libs/phirehose-master/lib/Phirehose.php";
require_once $dir."libs/StreamPhirehose.php";

function tst_log($message) {
    _log("Twitter Stream: " . $message);
}

class StreamConsumer extends StreamPhirehose{
    private $_follows;
    private $_pdo;
    private $_dsn;
    private $_db;

    public function _initialize($follows, $dsn, $db) {
        $this->_follows = $follows;
        $this->_dsn = $dsn;
        $this->_db = $db;
        $this->_pdo = $this->make_connection();
    }

    private function make_connection() {
        try {
            $pdo = new PDO($this->_dsn, $this->_db["user"], $this->_db["password"]);
            return $pdo;
        } catch (Exception $e) {
            tst_log("ERROR - Connection failed: " . $e->getMessage() . "\n");
            return FALSE;
        }
    }

    public function enqueueStatus($status) {
	$data = json_decode($status, TRUE);
        if (in_array($data["user"]["id"], $this->_follows)) {

            $count = 0;
            while ($this->_pdo === FALSE || $count > 5 || $this->_pdo === null) {
                $this->_pdo = $this->make_connection();
                $count++;

            }
            if ($this->_pdo === FALSE || $this->_pdo === null) {
                tst_log("ERROR - Unable to connect to PDO.\n");
                return;
            }

            $sql = "SELECT user_id FROM users WHERE content='{$data["user"]["id"]}'"
                . " AND content_type='" . TW_USER_ID . "'";
	    $user_id = 0;
            foreach ($this->_pdo->query($sql) as $row) {
                $user_id = $row["user_id"];
            }

            $raw = array($data);
            $value = "(" . $this->_pdo->quote($user_id) . ", "
                . $this->_pdo->quote(json_encode($raw)) . ", "
                . time() . ")";
         
            $sql = "INSERT INTO tw_cache (user_id, raw_content, date_created) "
                . "VALUES " . $value;

            $count = $this->_pdo->exec($sql);

            if ($count) {
                tst_log("INSERTED " . $count . " rows to tw_cache table, user "
                    . "$user_id\n");

            } else {
                tst_log(print_r($this->_pdo->errorInfo(), TRUE) . "\n");
            }
        }
        $this->_pdo = NULL;
    }
}

$pidFile = PID_LOCATION . "twitter_stream.pid";
$fh = fopen($pidFile, "w") or die("Can't open file");
fwrite($fh, getmypid());
fclose($fh);

set_time_limit(0);

$creds = array();
$follows = array();
while (count($follows) < 1) {
    try {
        $pdo = new PDO($dsn, $db["user"], $db["password"]);
        $sql = "SELECT name, content FROM oauth WHERE name in ('" . TW_KEY
            . "', '" . TW_SECRET . "', '" . TW_OAUTH_TOKEN . "', '"
            . TW_OAUTH_SECRET . "')";
        foreach ($pdo->query($sql) as $row) {
            $creds[strtolower($row["name"])] = $row["content"];
        }
        $users = "SELECT user_id, content FROM users WHERE content_type = '".TW_USER_ID."'";
        
	foreach ($pdo->query($users) as $row) {           
	    array_push($follows, $row["content"]);
        }
	
        $pdo = NULL;
    } catch (Exception $e) {
        tst_log("ERROR - Connection failed: " . $e->getMessage());
        exit;
    }
    if (count($follows) < 1) {
        tst_log("INFO - No users to follow. Sleeping for 1 minute, then check again.\n");
        sleep(60);
    }
}
$follow = implode(",", $follows);

define('TWITTER_CONSUMER_KEY', $creds[TW_KEY]);
define('TWITTER_CONSUMER_SECRET', $creds[TW_SECRET]);

define('OAUTH_TOKEN', $creds[TW_OAUTH_TOKEN]);
define('OAUTH_SECRET', $creds[TW_OAUTH_SECRET]);

$sc = new StreamConsumer(OAUTH_TOKEN, OAUTH_SECRET);
$sc->setParams($follow);
$sc->_initialize($follows, $dsn, $db);
$sc->consume();

?>
