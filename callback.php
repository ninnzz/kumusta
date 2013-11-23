<?php
	$insertClause = 'INSERT INTO %s VALUES(%s)';
	$table = 'users';

	if(isset($_REQUEST['code'])) {
		$response = $auth->getAccessToken($_REQUEST['code']);
	} else if(isset($_REQUEST['access_token'])) {
		$response = $_REQUEST;
	}

	if(isset($response['access_token'])) {
		$link = mysqli_connect("localhost","root","P@ssw0rd","kumusta") or die("Error " . mysqli_error($link));
		$data = array('NULL', $response['subscriber_number'], $response['access_token'], date('Y-m-d H:i:s'), 'NULL');
		$query = sprintf($insertClause, $table, implode(',', $data));

		$response = $link->query($query);

		print_r($response);
	}
?>