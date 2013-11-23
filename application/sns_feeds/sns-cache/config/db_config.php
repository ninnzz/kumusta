<?php

$db = array(
    "hostname" => "localhost",
    "user"     => "startrack-admin",
    "password" => '$t@rtr@ck',
    "db_name"  => "sns_cache"
);

$dsn = "mysql:dbname=" . $db["db_name"] . ";host=" . $db["hostname"];

?>
