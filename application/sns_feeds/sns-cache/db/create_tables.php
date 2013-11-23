<?php

require_once "/home/ubuntu/cheetos/application/sns_feeds/sns-cache/config/config.php";

$sql = <<<SQL
CREATE TABLE IF NOT EXISTS oauth (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    content VARCHAR(255) NOT NULL,
    date_updated INT(11) NOT NULL DEFAULT 0,
    UNIQUE(name)
);
CREATE TABLE IF NOT EXISTS users (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INTEGER UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    content_type VARCHAR(100) NOT NULL,
    content VARCHAR(255) NOT NULL,
    date_created INT(11) NOT NULL DEFAULT 0,
    UNIQUE(user_id, content_type),
    UNIQUE(content)
);
CREATE TABLE IF NOT EXISTS fb_cache (
    id BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INTEGER UNSIGNED NOT NULL,
    raw_content LONGTEXT,
    date_created INT(11) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
CREATE TABLE IF NOT EXISTS tw_cache (
    id BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INTEGER UNSIGNED NOT NULL,
    raw_content LONGTEXT,
    date_created INT(11) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);
CREATE TABLE IF NOT EXISTS parsed_contents (
    id BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INTEGER UNSIGNED NOT NULL,
    content_type VARCHAR(100) NOT NULL,
    post_id VARCHAR(100) NOT NULL,
    parsed_content LONGTEXT,
    post_date INT(11) NOT NULL,
    date_created INT(11) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

ALTER TABLE fb_cache ADD INDEX date_created (date_created);
ALTER TABLE tw_cache ADD INDEX date_created (date_created);
ALTER TABLE parsed_contents ADD INDEX user_id_idx (user_id);
ALTER TABLE parsed_contents ADD INDEX post_id_idx (post_id);
ALTER TABLE parsed_contents ADD INDEX post_date_idx (post_date);
SQL;

$creds = array(
    FB_SECRET       => "d481012df6d2e947e8442cc35d211fd3",
    FB_APP_ID       => "214855112027480",
    FB_ACCESS_TOKEN => "CAADDaNqhbVgBAKfxPjjLkCcmmCWaOVRZAkY5JSpLIZBziZCRxix9QgNCR1IZCu0JgWyZCPqbwdwHjlZC1U6ovSg6oGIcB0BK8KUlrT9GdtA3IDAocZCZC7ko3sK6rfN1ZAXSe6sZBIxqS27Fkch2WO788yvxIY0VsFw2V8gEwiCZCBCrKD5JmXV90bpaxE1ZB8IuHQgZD",
    TW_KEY          => "YGsWC42uPSbKN7UTO8Qlg",
    TW_SECRET       => "8V3DQSnzYYudneWACPHiewXLVwEXDOHwYni5NFPs",
    TW_OAUTH_TOKEN  => "2190619520-lmj8aeP0mjXFWOH8feFGA144qaBPJMLjlbAy7kF",
    TW_OAUTH_SECRET => "2SO03jgYn31wJEZyXkaQI48MfX56Ktbo8fM7G2URiFfUB",
);

try {
    $pdo = new PDO($dsn, $db["user"], $db["password"]);
    $result = $pdo->exec($sql);

    $values = array();
    foreach ($creds as $key => $value) {
        $value = "(" . $pdo->quote($key) . ", " . $pdo->quote($value) . ", "
            . time() . ")";
        $values[] = $value;
    }
    $value = implode(", ", $values);
    $insert = "INSERT IGNORE INTO oauth (name, content, date_updated) VALUES "
        . $value;
    $count = $pdo->exec($insert);
    echo "Inserted $count rows to oauth table.\n";
    echo "Done.\n";
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
    echo "\n";
}

?>
