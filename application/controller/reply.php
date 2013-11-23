<?php
class Reply extends Kiel_Controller
{
	public function index_post(){
		$required = array('app_id','message','number');
		$this->required_fields($required,$this->post_args);

		$this->checkAuth($this->post_args['app_id']);
		$data = $this->post_args;


		$key = '6qxBuD5aUyrwvscpkFt9';
		$number = $data['number'];
		$message = urlencode($data['message']).' [This is an auto generated message, DO NOT REPLY! You can reply by sending your reply to 260011.]';
		$from = 'ReliefBoard';
		$url = 'http://api.semaphore.co/api/sms';

		$fields_string = "api={$key}&number={$number}&message={$message}&from={$from}";
		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_POST, 4);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

		//execute post
		$result = curl_exec($ch);

		//close connection
		curl_close($ch);

		print_r($result);

	}
}

?>

