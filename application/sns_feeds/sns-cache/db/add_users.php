<?php


$indexes = array("user_id", "name", "content_type", "content");

try {
   $db = array(
        "hostname" => "localhost",
        "user"     => "root",
        "password" => '1234',
        "db_name"  => "buzzbie"
    );

    $dsn = "mysql:dbname=" . $db["db_name"] . ";host=" . $db["hostname"];
    $pdo = new PDO($dsn, $db["user"], $db["password"]);
    $sql = "SELECT id,name,data FROM places";
    foreach ($pdo->query($sql) as $row) {   
        if(!empty(json_decode($row['data'])->{'facebook_page_id'}))
        {
            $v[] = "(".$row['id'].",".$pdo->quote($row['name']).',"fb_post",'.$pdo->quote(json_decode($row['data'])->{'facebook_page_id'}).",".time().")";
        }
        if(!empty(json_decode($row['data'])->{'twitter_hashtag'}))
        {
            $v[] = "(".$row['id'].",".$pdo->quote($row['name']).',"twitter_post",'.$pdo->quote(json_decode($row['data'])->{'twitter_hashtag'}).",".time().")";   
        }
        if(!empty(json_decode($row['data'])->{'instagram_hashtag'}))
        {
            $v[] = "(".$row['id'].",".$pdo->quote($row['name']).',"instagram_post",'.$pdo->quote(json_decode($row['data'])->{'instagram_hashtag'}).",".time().")";   
        }   
    }

    $values = implode(",", $v);
    $insert_count = -1;
    $dsn = "mysql:dbname=sns_cache;host=" . $db["hostname"];
    $pdo = new PDO($dsn, $db["user"], $db["password"]);
    if (count($v) > 0) {
        $sql = "INSERT INTO users (user_id, name, content_type, content, date_created) "
            . "VALUES " . $values;
        $insert_count = $pdo->exec($sql);
     }

    echo "Inserted $insert_count users.\n";
    echo "Error: " . json_encode($pdo->errorInfo()) . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
$pdo = NULL;

?>
