<?php
class Messages extends Kiel_Controller{

	public function feed_get()
	{
		$this->load_model('feed_model');
		$offset    = isset($this->get_args['offset'])?$this->get_args['offset']:0;
		$limit     = isset($this->get_args['limit'])?$this->get_args['limit']:10;
		$parent_id = isset($this->get_args['parent_id'])?$this->get_args['parent_id']:NULL; 
		
		if(isset($this->get_args['parent_id'])){
			$parent_id = $this->get_args['parent_id']; 
		} else{
			$parent_id = NULL; 
		}

		if(isset($this->get_args['source'])){
			$source = trim(strtolower($this->get_args['source'])); 
		} else{
			$source = NULL;
		}

		if(empty($limit)){
			$offset = 0;
			$limit  = 10;
		}
		$res  = $this->feed_model->get_messages($parent_id ,$offset ,$limit,$source);
		$this->response(array('status'=>'Success','data'=>$res),200);
	}

	public function feed_item_get()
	{
		$this->required_fields(array('message_id'),$this->get_args);
			$this->load_model('feed_model');
			$res = $this->feed_model->single_item($this->get_args['message_id']);
			$this->response(array('status'=>'Success','data'=>$res),200);
	}

	public function feed_post()
	{
		$required = array('app_id','message','name');
		$this->required_fields($required,$this->post_args);
		$this->checkAuth($this->post_args['app_id']);

		$this->load_model('feed_model');
		$user_no = $this->post_args['user_number'];
		$addr = $this->post_args['address'];
		$name = $this->post_args['name'];
		$message = $this->post_args['message'];
		/*******optional parameters*************/
		$app_id = isset($this->post_args['app_id'])?$this->post_args['app_id']:"";		
		$fb = isset($this->post_args['fb_id'])?$this->post_args['fb_id']:'';		
		$tags = isset($this->post_args['tags'])?$this->post_args['tags']:'';		
		$expire = isset($this->post_args['expires'])?$this->post_args['expires']:NULL;		

		$parent_id = isset($this->post_args['parent_id'])?$this->post_args['parent_id']:NULL;
		// $this->push_post($user_no,$addr,$name,$message,$app_id,$parent_id);
		$res = $this->feed_model->add_messages($user_no,$addr,$name,$message,$app_id,NULL,$parent_id,$fb,$tags,$expire);	

		if($res)		
		{
			$message = urldecode($message);
			$this->sns_crosspost($message,$name,$addr,$res['id']);
			$this->post_to_sms($message,$res['id']);
		}

		$this->response(array('status'=>'Success'),200);

	}


	public function search_get()
	{
		$offset = $this->get_args['offset'];
		$limit  = $this->get_args['limit'];
		
		if(empty($limit)){
			$offset = 0;
			$limit  = 10;
		}

		if($this->get_args['q'] && isset($this->get_args['q']) && $this->get_args['q'] != ""){
			$this->load_model('feed_model');
			$res = $this->feed_model->search($this->get_args['q'], $offset, $limit);
			$this->response(array('status'=>'Success','data'=>$res),200);

		} else {

		}
	}

	public function feed_callback_semaphore_post()
	{
		$this->load_model('feed_model');
		$data = $this->post_args;

		$smsMsg = $data['message'];
		$user_no = $data['number'];
		$id = $data['message_id'];

		$msg_arr = explode('/',urldecode($smsMsg));

		/*****FOR GOHELP******/

		if(isset($msg_arr[0])){
			$key_word = explode(' ',trim($msg_arr[0]));
			if(trim(strtolower($key_word[0])) === 'gohelp'){
				$source_type = null;
				$source = 'GOHELP';
				if(count($msg_arr) === 3 ){
					$addr = isset($key_word[1])?$key_word[1]:"";
					$name = $msg_arr[1];
					$message = $msg_arr[2];
					$res = $this->feed_model->add_messages($user_no,$addr,$name,$message,$source,$source_type,null,'','',null);
				} else if(count($msg_arr) === 2){
					$addr = isset($key_word[1])?$key_word[1]:"";
					$message = $msg_arr[1];
					
					$res = $this->feed_model->add_messages($user_no,$addr,null,$message,$source,$source_type,null,'','',null);
				} else{
					if(trim($smsMsg) !== ""){
						$message = $smsMsg;
						$res = $this->feed_model->add_messages($user_no,null,null,$message,$source,null,null,'','',null);
					}
				}

			}




			/*****FOR RELIEFBOARD******/

			else {
				if(count($msg_arr) === 3){
					$addr = $msg_arr[0];
					$name = $msg_arr[1];
					$message = $msg_arr[2];

					$res = $this->feed_model->add_messages($user_no,$addr,$name,$message,'sms.semaphore',null,null,'','',null);
				} else if(count($msg_arr) === 2){
					$addr = $msg_arr[0];
					$message = $msg_arr[1];

					$res = $this->feed_model->add_messages($user_no,$addr,null,$message,'sms.semaphore',null,null,'','',null);
				} else {
					if(trim($smsMsg) !== ""){
						$message = $smsMsg;
						$res = $this->feed_model->add_messages($user_no,null,null,$message,'sms.semaphore',null,null,'','',null);
					}
				}
			}
		}

		if($res)		
		{	
			$message = urldecode($message);
			$this->sns_crosspost($message,$name,$addr,$res['id']);
		}
	
		$this->response(array('status'=>'Success','data'=>''),200);
	}
	
	public function feed_callback_smart_get()
	{
		$this->load_model('feed_model');

		$data 	 = $this->get_args;
		$user_no = $data['from'];
		$smsMsg  = urldecode($data['text']);
		$msg_arr = explode('/',$smsMsg);
		if(count($msg_arr) === 3){
			$addr = $msg_arr[0];
			$name = $msg_arr[1];
			$message = $msg_arr[2];

			$res = $this->feed_model->add_messages($user_no,$addr,$name,$message,'sms.smart',null,null,'','',null);
		} else if(count($msg_arr) === 2){
			$addr = $msg_arr[0];
			$message = $msg_arr[1];

			$res = $this->feed_model->add_messages($user_no,$addr,null,$message,'sms.smart',null,null,'','',null);
		} else {
			if(trim($smsMsg) !== ""){
				$message = $smsMsg;
				$res = $this->feed_model->add_messages($user_no,null,null,$message,'sms.smart',null,null,'','',null);
			}
		}

		if($res)		
		{	
			$message = urldecode($message);
			$this->sns_crosspost($message,$name,$addr,$res['id']);
		}
	}

	public function feed_callback_post()
	{
		$this->load_model('feed_model');

		/*********MESSAGE PART******************/
		$xml = simplexml_load_file('php://input');
		$sms = array();
		$nodes = $xml->xpath('/message/param');

		foreach($nodes as $node) {
		   $param = (array) $node;
		   $sms[$param['name']] = $param['value'];
		}

		if($sms['messageType'] == 'SMS') {
		   $user_no = $sms['source'];
		   $smsMsg = $sms['msg'];
		} else{
		   die("Invalid message type");
		}



		$msg_arr = explode('/',$smsMsg);

		/*****FOR HYCH******/

		if(isset($msg_arr[0])){
			$key_word = explode(' ',trim($msg_arr[0]));
			if(trim(strtolower($key_word[0])) === 'hych'){
				$source_type = trim(strtolower($key_word[1]));
				$source = 'HYCH';
				if(count($msg_arr) === 5 ){
					$addr = $msg_arr[3];
					$name = $msg_arr[1];
					$message = $msg_arr[4];	
					$user_no = $msg_arr[2];	
					$res = $this->feed_model->add_messages($user_no,$addr,$name,$message,$source,$source_type,null,'','',null);
				} else if(count($msg_arr) === 4){
					$message = $msg_arr[1].'/'.$msg_arr[2].'/'.$msg_arr[3];
					$res = $this->feed_model->add_messages(null,null,null,$message,$source,$source_type,null,'','',null);
				} else if(count($msg_arr) === 3){
					$message = $msg_arr[1].'/'.$msg_arr[2];
					$res = $this->feed_model->add_messages(null,null,null,$message,$source,$source_type,null,'','',null);
				} else if(count($msg_arr) === 2){
					$message = $msg_arr[1];
					$res = $this->feed_model->add_messages(null,null,null,$message,$source,$source_type,null,'','',null);
				} else{
					$res = false;
				}

			}


			/***For relief board***/
			else {

				if(count($msg_arr) === 3){
					$addr = $msg_arr[0];
					$name = $msg_arr[1];
					$message = $msg_arr[2];

					$res = $this->feed_model->add_messages($user_no,$addr,$name,$message,'sms.globe',null,'','',null);
				} else if(count($msg_arr) === 2){
					$addr = $msg_arr[0];
					$message = $msg_arr[1];

					$res = $this->feed_model->add_messages($user_no,$addr,null,$message,'sms.globe',null,'','',null);
				} else {
					if(trim($smsMsg) !== ""){
						$message = $smsMsg;
						$res = $this->feed_model->add_messages($user_no,null,null,$message,'sms.globe',null,'','',null);
					}
				}
			}
			/******Relief Board end******/

		}

		
		if($res)		
		{	
			$message = urldecode($message);
			$this->sns_crosspost($message,$name,$addr,$res['id']);
		}
	}

	public function message_flag_post()
	{
		$this->load_model('feed_model');

		$data = $this->post_args;
		$res = $this->feed_model->update_status($data);
		
		$this->response(array('status'=>'Success'),200);	
	}

	private function make_bitly_url($url,$login,$appkey,$format = 'xml',$version = '2.0.1') 
	{
		//create the URL
		$bitly = 'http://api.bit.ly/shorten?version='.$version.'&longUrl='.urlencode($url).'&login='.$login.'&apiKey='.$appkey.'&format='.$format;
		//get the url
		//could also use cURL here
		$response = file_get_contents($bitly);
		
		//parse depending on desired format
		if(strtolower($format) == 'json')
		{
			$json = @json_decode($response,true);
			return $json['results'][$url]['shortUrl'];
		}
		else //xml
		{
			$xml = simplexml_load_string($response);
			return 'http://bit.ly/'.$xml->results->nodeKeyVal->hash;
		}
	}

	private function sns_crosspost($message,$sender,$loc,$id)
	{

		$sender = urldecode($sender);
		$loc = urldecode($loc);
		
		if( trim($sender) != "" )
			$sender = $sender . " - ";

		if( trim($loc) != "" )
			$loc = $loc . ' - ' ;

		$url = "http://www.reliefboard.com/ph/post.php?id=". $id;

		// ACCESS TOKEN BITLY = ad38c591217caccf37cbed3b4e98b36470c4cf53
		
		$bitly = $this->make_bitly_url($url,'kjventura','R_afc197795cfaf9242fc1063b2c77c48d','json');

		error_log( "THE BITLY ADDRESS IS: " . $bitly );
		
		$message = "#Yolanda #Haiyan - " . $sender . $loc . $bitly . " - " . $message;

		$message = substr($message, 0, 135) . "...";

		$params['facebook_access_token'] = 'CAADDaNqhbVgBAHJqjx4fqE8iN006WvF9tBoJK9s7DWy5UAM4RMWyhiMGxQOyuMR32uYhZBrUlx42Jv9SOefXh2JA051xig8l2TAd5XymykksQD3ximfthOXl2CnSlY3KaqFDtbZBuz1WOFI3ZAVaY9U9FLiZCugYCUhVZBjzeJbRXeM2EIos9QXO0azcCE6EZD';
		//https://graph.facebook.com/oauth/access_token?client_id=214855112027480&client_secret=d481012df6d2e947e8442cc35d211fd3&grant_type=fb_exchange_token&fb_exchange_token=
		$params['twitter_access_token']  = '2190619520-lmj8aeP0mjXFWOH8feFGA144qaBPJMLjlbAy7kF';
		$params['twitter_access_secret'] = '2SO03jgYn31wJEZyXkaQI48MfX56Ktbo8fM7G2URiFfUB';
		$params['place'] 				 = '454373604683875';
		$params['message'] 				 = $message;
		$url 							 = 'http://api.buzzboarddev.stratpoint.com/posts/v1/fb_post'; 
		
		$this->curl($url, $params);
	}

	private function post_to_sms($message,$parent_id)
	{
		$params['message']  = $message;
		$params['msg_id'] 	= $parent_id; 
		$url 				= 'http://tma.herokuapp.com/notify'; 
		
		$this->curl($url, $params);
	}

	private function push_post($user_no,$addr,$name,$message,$app_id,$parent_id)
	{
		$params['user_no']  = $user_no;
		$params['addr'] 	= $addr;
		$params['app_id'] 	= $app_id;
		$params['message']  = $message;
		$params['msg_id'] 	= $parent_id; 
		$url 				= 'http://push.buzzboard.stratpoint.com:8886/message/add'; 
		
		$this->curl($url, $params);
	}

	private function curl($url, $params) {
		$ch = curl_init();
			curl_setopt($ch, CURLOPT_HEADER, 0);
		    curl_setopt($ch, CURLOPT_VERBOSE, 0);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
		    curl_setopt($ch, CURLOPT_URL, $url);
		    curl_setopt($ch, CURLOPT_POST, true);

		    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		    curl_exec($ch);
	}
}