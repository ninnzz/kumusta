<?php
class Cron_subscribe extends Kiel_Controller
{
	private function sendTo($id, $number, $entry)
	{
		$key = '6qxBuD5aUyrwvscpkFt9';
		$number = $number;
		$message = "Magandang Araw! Kung ikaw ay may alam na impormasyong tungkol kay {$entry}, maaraing tumawag sa 21581001 upang makausap mo ang taong naghahanap sa kanya. Ang iyong NUMBER CODE ay : {$id}";
		$message = urlencode($message).' [This is an auto generated message, DO NOT REPLY! Tumawag sa 21581001 at ilagay ang number code.]';
		$from = 'KumustaKNB';
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
		
	}


	public function index_get()
	{
		$required = array('app_id');
		$this->required_fields($required,$this->get_args);
		if($this->get_args['app_id'] != "90an729m.kumusta.web"){
			$this->response(array('status'=>'You dont have permission to access this URL'),500);
		}
		$this->load_model('subscriber_model');

		$res = $this->subscriber_model->all_subscribed();


		foreach($res['result'] as $bogart){
		// put here the function for searching just loop through the result and send message
		//pag nakakita ka ng number, may tatawagin kang API..
		$reply_string  ="";

		$ret = array();
		$count=0;
		$tmp = array();

		$fb = array();
		$google=array();
		$dswd=array();
		$relief = array();
		$bangon = array();


		$url = 'http://graph.facebook.com/666212400066213/photos?fields=name,tags,source,link&limit=1000';
		$response = file_get_contents($url);
		if($response){
		$array = json_decode($response, true);
		foreach($array['data'] as $p){
				$name = "";
				if(isset($p['tags'])) {
					foreach($p['tags']['data'] as $q){
						$name =$name . $q['name'].' & ';
					}
				}
				if(preg_match
					(
					strtolower('/('.preg_replace('[ \t\n]','|',urldecode($bogart['searchString'])).')/'),
					strtolower($name.' '.$p['name'])
					)
					
				){
				array_push($fb, array(
					'place' => '',
					'sender' => strstr($p['name'], 'Help', true) . ' & '.$name,
					'number' => '',
					'message' => strstr($p['name'], 'Help'),
					'image' => $p['source'],
					'from' => $p['link'],
					's_type' => 'facebook'
				));
				}
		}

		$ret['fb'] = $fb;


		}
		$url = 'https://www.google.org/personfinder/2013-yolanda/api/search?key=smo7n6_B3sgRMD9Y&q='.urlencode($bogart['searchString']);
		$response = file_get_contents($url);
		if($response){
		$data = preg_replace("/pfif\:/", "", $response);
		$xml = simplexml_load_string($data);
		$json = json_encode($xml);
		$array = json_decode($json,TRUE);
		
		
		foreach($array['person'] as $p){
			$mess = "";
			if(isset($p['note'])){
				foreach($p['note'] as $g)
					$mess = $g['status'] ."\n" .$g['text'];
			}

			$matches = array();
			preg_match("/((\+63|0|)9(05|06|15|16|17|26|27|35|36|37|94|96|97)[0-9]{7,7})/", $p['description'], $matches);
			if(preg_match(str_ireplace(" ","|",strtolower(" ".$p['home_street'].' '.$p['home_city'].' '. $p['home_state']." ".$p['full_name'] .' ' .$p['alternate_names'].' '.$p['given_name'].' '.$p['family_name']." ".preg_replace("/(\r\n|\r|\n)/", "", $p['description'] . $mess))), strtolower($bogart['searchString'])) && $matches[0]) $this->sendTo($bogart['userId'],$matches[0],$bogart['searchString']);
				$this->sendTo($bogart['userId'],$matches[0],$bogart['searchString']);
			array_push($google, array(
				'place' => "".$p['home_street'].' : '.$p['home_city']	.' : '. $p['home_state'],
				'sender' => "".$p['full_name'] .' ' .$p['alternate_names'].' : '.$p['given_name'].$p['family_name'],
				'number' => "".$matches[0],
				'message' => "".preg_replace("/(\r\n|\r|\n)/","", $p['description'] . $mess),
				'from' => "".$p['source_url'],
				's_type' => 'google'
			));
		

		}
		}

		
		$ret['google'] = $google;

		$tmp = array();
		$url = 'http://cors.io/spreadsheets.google.com/feeds/list/0ApSfq4LnrdaRdHBTSllLTVBaSW9UTjlobUZCNXRNN1E/od6/public/values?alt=json&q='.urlencode($bogart['searchString']);
		$response = file_get_contents($url);
		if($response){
		$array = json_decode($response, true);
		$data = $array['feed']['entry'];
		foreach($data as $p){

			if(preg_match(strtolower('/('.strtolower(str_ireplace(' ','|',$p['gsx$firstname']['$t']).'|'. str_ireplace(' ','|',$p['gsx$lastname']['$t'])). ')/'), strtolower($bogart['searchString'])))
			if(!isset($tmp[$p['gsx$firstname']['$t'].' '.$p['gsx$lastname']['$t']])){
				array_push($dswd, array(
					'place' => '',
					'sender' =>	$p['gsx$firstname']['$t'].' '.$p['gsx$lastname']['$t'],
					'number' => '',
					'message' => 'Survivor',
					'from' => $p['id']['$t'],
					's_type' => 'dswd'
				));
				$tmp[$p['gsx$firstname']['$t'].' '.$p['gsx$lastname']['$t']] = true;
			}

			if(preg_match(strtolower('/('.strtolower(str_ireplace(' ','|',$p['gsx$firstname_2']['$t']).'|'. str_ireplace(' ','|',$p['gsx$lastname_2']['$t'])). ')/'), strtolower($bogart['searchString'])))
			if(!isset($tmp[$p['gsx$firstname_2']['$t'].' '.$p['gsx$lastname_2']['$t']])){
			array_push($dswd, array(
				'place' => '',
				'sender' =>	$p['gsx$firstname_2']['$t'].' '.$p['gsx$lastname_2']['$t'],
				'number' => '',
				'message' => 'Survivor',
				'from' => $p['id']['$t'],
				's_type' => 'dswd'
			));
			$tmp[$p['gsx$firstname_2']['$t'].' '.$p['gsx$lastname_2']['$t']] = true;
			}

			if(preg_match(strtolower('/('.strtolower(str_ireplace(' ','|',$p['gsx$firstname_3']['$t']).'|'. str_ireplace(' ','|',$p['gsx$lastname_3']['$t'])). ')/'), strtolower($bogart['searchString'])))
			if(!isset($tmp[$p['gsx$firstname_3']['$t'].' '.$p['gsx$lastname_3']['$t']])){
			array_push($dswd, array(
				'place' => '',
				'sender' =>	$p['gsx$firstname_3']['$t'].' '.$p['gsx$lastname_3']['$t'],
				'number' => '',
				'message' => 'Survivor',
				'from' => $p['id']['$t'],
				's_type' => 'dswd'
			));
			$tmp[$p['gsx$firstname_3']['$t'].' '.$p['gsx$lastname_3']['$t']] = true; 
			}

			if(	preg_match(strtolower('/('.strtolower(str_ireplace(' ','|',$p['gsx$firstname_4']['$t']).'|'. str_ireplace(' ','|',$p['gsx$lastname_4']['$t'])). ')/'), strtolower($bogart['searchString'])))
			if(!isset($tmp[$p['gsx$firstname_4']['$t'].' '.$p['gsx$lastname_4']['$t']])){
			array_push($dswd, array(
				'place' => '',
				'sender' =>	$p['gsx$firstname_4']['$t'].' '.$p['gsx$lastname_4']['$t'],
				'number' => '',
				'message' => 'Survivor',
				'from' => $p['id']['$t'],
				's_type' => 'dswd'
			));
			$tmp[$p['gsx$firstname_4']['$t'].' '.$p['gsx$lastname_4']['$t']] = true;
			}
		}

		}


		$ret['dswd'] = $dswd;

		$url = 'http://reliefboard.com/search?loc=1&name=1&message=1&query='.urldecode($bogart['searchString']).'&offset=0&limit=1000000000';
		$response = file_get_contents($url);
		if($response){
		$json = stripslashes($response);
		$array = json_decode($json, true);
		if($array['data']['result_count'] > 0){
		$data = $array['data']['result'];
		foreach($data as $p){
			$matches = array();

			preg_match("/((\+63|0|)9(05|06|15|16|17|26|27|35|36|37|94|96|97)[0-9]{7,7})/", $p['sender_number'], $matches);
			if($matches[0])
				$this->sendTo($bogart['userId'],$matches[0],$bogart['searchString']);
			array_push($relief, arrat(
				'id' => $p['id'],
				'place' => urldecode($p['place_tag']),
				'sender' => urldecode($p['sender']),
				'number' => $matches[0],
				'message' => urldecode($p['message']),
				'from' => $url,
				's_type' => 'relief'
			));
		}}
		}
		$ret['relief'] = $relief;

		$url = 'http://api.bangonph.com/v1/posts';
		$response = file_get_contents($url);
		if($response){
		$array = json_decode($response, TRUE);
		$data = $array['data']['posts'];
		foreach($data as $p){
			if(preg_match(strtolower('/('.strtolower(str_ireplace(' ','|',$p['location'].' '.$p['name'].' '.$p['message'] )). ')/'), strtolower(urldecode($bogart['searchString']))))
			if(strpos(strtolower($p['location'].$p['name'].$p['message']), strtolower($bogart['searchString']))) {
			$matches = array();
			preg_match("/((\+63|0|)9(05|06|15|16|17|26|27|35|36|37|94|96|97)[0-9]{7,7})/", $p['phone'], $matches);
			if(preg_match('/('.str_ireplace(" ","|",strtolower($p['location'].' '.$p['name'].' '.$p['message'])). ')/', $bogart['searchString']) && $matches[0])
				$this->sendTo($bogart['userId'],$matches[0],$bogart['searchString']);
				array_push($bangon, array(
					'id'	=> $p['id'],
					'place' => $p['location'],
					'sender' => $p['name'],
					'number' => $matches[0],
					'message' => $p['message'],
					'from' => $url,
					's_type' => 'bangon'
				));
			}
		}
		}
	
		$ret['bangon'] = $bangon;

		}
		$rt = array_slice(array_merge($fb, $google,$dswd,$relief,$bangon), 0 ,3);

		

		$this->response(array('status'=>'Success','data'=>$res),200);


	}

}

?>
