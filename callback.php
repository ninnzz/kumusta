<?php
	include('PHP/src/GlobeApi.php');
	$insertClause = 'INSERT INTO %s VALUES(%s)';
	$table = 'users';

	$globe = new GlobeApi();
	$auth = $globe->auth(
	    'g4Ry4sK4xGEu67Teo8cxxzu8oR4gsk4z',
	    'd8d9dc0d92941ff23b9b7f151f9162ec814afaaae31533d5ddda66c2c2587ee5'
	);

	if(isset($_POST['message']) && isset($_POST['phone_number'])) {
		$link = mysqli_connect("localhost","root","P@ssw0rd","kumusta") or die("Error " . mysqli_error($link));
		$result = $link->query('SELECT * FROM users WHERE phoneNumber = \''.str_replace('tel:+63', '', $_POST['phone_number']).'\' LIMIT 1;');
		$user = $result->fetch_row();

		$i = 0;
		$msglen = strlen($_POST['message']);
		$newstr = substr($_POST['message'], 0, 150);
		$nummes = ceil(strlen($_POST['message'])/150);
		do {
			$sms = $globe
				->sms(7625)
				->sendMessage(
					$user['2'],
					$user['1'],
					'('.++$i.'/'.$nummes.') '.$newstr
				);
			$newstr = substr($_POST['message'], $i*150, 150);
		} while ($i*150 < $msglen);

		mysqli_close($link); 
	}

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