<?php
	$insertClause = 'INSERT INTO %s VALUES(%s)';
	$table = 'users';

	if($_REQUEST['code']) {
		$response = $auth->getAccessToken($_REQUEST['code']);
	} else if($_REQUEST['access_token']) {
		$response = $_REQUEST;
	}


	$link = mysqli_connect("localhost","root","P@ssw0rd","kumusta") or die("Error " . mysqli_error($link));
	print_r($link);

	if(isset($response['access_token'])) {
		$data = array('NULL', $response['subscriber_number'], $response['access_token'], 'NULL', 'NULL');
		$query = sprintf($insertClause, $table, implode(',', $data));
	}
?>