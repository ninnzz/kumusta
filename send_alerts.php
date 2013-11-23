<?php
	$url = "http://ec2-184-169-205-217.us-west-1.compute.amazonaws.com/cron_subscribe?app_id=90an729m.kumusta.web";
	$json = file_get_contents($url);
	$array = json_decode($json, true);
	var_dump($array);

?>
