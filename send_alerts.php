<?php
	$url = "http://ec2-184-169-205-217.us-west-1.compute.amazonaws.com/cron_subscribe?app_id=90an729m.kumusta.web";
	$json = file_get_contents($url);
	$array = json_decode($json, true);
	foreach($array['data']['result'] as $target){
		$q = 'http://ec2-184-169-205-217.us-west-1.compute.amazonaws.com/search?source=subscribe&query='.strtolower($target['searchString']).'&id='.$target['userId'];

	}

?>
