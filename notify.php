<?php
include('PHP/src/GlobeApi.php');

$globe = new GlobeApi();
$sms = $globe->sms(7625);

// $test = array(1,2,3,4,5);
// print_r(array_splice($test, 1));

$json = file_get_contents('php://input');
$json = stripslashes($json);
$message = json_decode($json, true);

//if we received a message
if($message) {
	if(!isset($message['inboundSMSMessageList']['inboundSMSMessage'])) {
		return 'Not Set inboundSMSMessage';
	}

	$link = mysqli_connect("localhost","root","P@ssw0rd","kumusta") or die("Error " . mysqli_error($link));
	foreach($message['inboundSMSMessageList']['inboundSMSMessage'] as $item) {
		if(!isset($item['message'], $item['senderAddress'])) {
			continue;	
		}
		$result = $link->query('SELECT * FROM users WHERE phoneNumber = \''.str_replace('tel:+63', '', $item['senderAddress']).'\' LIMIT 1;');
		$user = $result->fetch_row();

		if($user) {
			if(strpos(strtoupper($item['message']), 'SEARCH') === 0) {
				$name = split(" ", strtoupper($item['message']));
				$name = implode(" ", array_splice($name, 1));
				//if searching
				$response = $sms->sendMessage(
					$user['2'],
					$user['1'],
					'You will be receiving the list containing '.$name
				);
				//logic for pull here
			}

			if(strpos(strtoupper($item['message']), 'SUBSCRIBE SEARCH') === 0) {
				$name = split(" ", strtoupper($item['message']));
				$name = implode(" ", array_splice($name, 2));
				//if subscribing to search
				$sms->sendMessage(
					$user['2'],
					$user['1'],
					'You will be receiving the list containing your '.$name.' every <time interval here>'
				);
				$query = 'INSERT INTO search VALUES(%s);';
				$data = array('NULL', '\''.$user[0].'\'', '\''.$name.'\'', '\''.date('Y-m-d H:i:s').'\'','NULL');

				$query = sprintf($query, implode(',', $data));
				$response = $link->query($query);
				//logic for adding to cron job here
			}

			if(strpos(strtoupper($item['message']), 'END SUBSCRIBE SEARCH') === 0) {
				$name = split(" ", strtoupper($item['message']));
				$name = implode(" ", array_splice($name, 3));
				//if ending subscription
				$sms->sendMessage(
					$user['2'],
					$user['1'],
					'You have successfully ended your subscription for updates about '.$name
				);

				$query = 'DELETE FROM search WHERE %s;';
				$query = sprintf($query, 'userId = '.$user[0].' AND searchString = \''.$name.'\'');
				$response = $link->query($query);

			}

			if(strpos(strtoupper($item['message']), 'DONATE') === 0) {
				$result = $link->query('SELECT * FROM donations ORDER BY id DESC LIMIT 1;');
				$donation = $result->fetch_row();
				//if donating
				$charge = $globe->payment(
				    $user['2'],
					$user['1']
				);

				$response = $charge->charge(
				    0,
				    '7625'.$donation[0]+1
				);

				if(!$response['error']) {
					$query = 'INSERT INTO donations VALUES(%s);';
					$data = array('NULL', $user[0]);
					$query = sprintf($query, implode(',', $data));
					$response = $link->query($query);
				}
			}
		}
	}

	mysqli_close($link); 
}
?>