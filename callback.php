<?php
	$insertClause = 'INSERT INTO %s VALUES(%s)';
	$table = 'users';

	$globe = new GlobeApi();
	$auth = $globe->auth(
	    'g4Ry4sK4xGEu67Teo8cxxzu8oR4gsk4z',
	    'd8d9dc0d92941ff23b9b7f151f9162ec814afaaae31533d5ddda66c2c2587ee5'
	);

	if(isset($_REQUEST['code'])) {
		$response = $auth->getAccessToken($_REQUEST['code']);
	} else if(isset($_REQUEST['access_token'])) {
		$response = $_REQUEST;
	}

	if(isset($response['access_token'])) {
		$link = mysqli_connect("localhost","root","P@ssw0rd","kumusta") or die("Error " . mysqli_error($link));
		$data = array('NULL', '\''.$response['subscriber_number'].'\'', '\''.$response['access_token'].'\'', '\''.date('Y-m-d H:i:s').'\'', 'NULL');
		$query = sprintf($insertClause, $table, implode(',', $data));

		$response = $link->query($query);
	}
?>