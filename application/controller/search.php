<?php
class Search extends Kiel_Controller
{
	public function index_get(){
		$required = array('query');
		$this->required_fields($required,$this->get_args);
		$searchString = $this->get_args['query'];


		$ret = array();
		$url = 'http://reliefboard.com/search?loc=1&name=1&message=1&query='.$searchString.'&offset=0&limit=1000000000';
		$response = file_get_contents($url);
		if($response){
		$json = stripslashes($response);
		$array = json_decode($json, true);
		if($array['data']['result_count'] > 0){
		$data = $array['data']['result'];
		foreach($data as $p){
			array_push($ret, array(
				'place' => $p['place_tag'],
				'sender' => $p['sender'],
				'number' => $p['sender_number'],
				'message' => $p['message'],
				'from' => $url
			));
		}}
		}
		
		$url = 'http://api.bangonph.com/v1/posts';
		$response = file_get_contents($url);
		if($response){
		$array = json_decode($response, TRUE);
		$data = $array['data']['posts'];
		foreach($data as $p){
			if(strpos(strtolower($p['location'].$p['name'].$p['message']), strtolower($searchString))) {
				array_push($ret, array(
					'place' => $p['location'],
					'sender' => $p['name'],
					'number' => $p['phone'],
					'message' => $p['message'],
					'from' => $url
				));
			}
		}
		}


		$tmp = array();
		$url = 'http://cors.io/spreadsheets.google.com/feeds/list/0ApSfq4LnrdaRdHBTSllLTVBaSW9UTjlobUZCNXRNN1E/od6/public/values?alt=json&q='.$searchString;
		$response = file_get_contents($url);
		if($response){
		$array = json_decode($response, true);
		$data = $array['feed']['entry'];
		foreach($data as $p){

			if(strpos(strtolower($p['gsx$firstname']['$t'].' '.$p['gsx$lastname']['$t']), strtolower($searchString))!== false)
			if(!isset($tmp[$p['gsx$firstname']['$t'].' '.$p['gsx$lastname']['$t']])){
			array_push($ret, array(
				'place' => '',
				'sender' =>	$p['gsx$firstname']['$t'].' '.$p['gsx$lastname']['$t'],
				'number' => '',
				'message' => 'Survivor',
				'from' => $p['id']['$t']
			));
			$tmp[$p['gsx$firstname']['$t'].' '.$p['gsx$lastname']['$t']] = true;
			}

			if(strpos(strtolower($p['gsx$firstname_2']['$t'].' '.$p['gsx$lastname_2']['$t']), strtolower($searchString))!== false)
			if(!isset($tmp[$p['gsx$firstname_2']['$t'].' '.$p['gsx$lastname_2']['$t']])){
			array_push($ret, array(
				'place' => '',
				'sender' =>	$p['gsx$firstname_2']['$t'].' '.$p['gsx$lastname_2']['$t'],
				'number' => '',
				'message' => 'Survivor',
				'from' => $p['id']['$t']
			));
			$tmp[$p['gsx$firstname_2']['$t'].' '.$p['gsx$lastname_2']['$t']] = true;
			}

			if(strpos(strtolower($p['gsx$firstname_3']['$t'].' '.$p['gsx$lastname_3']['$t']), strtolower($searchString))!== false)
			if(!isset($tmp[$p['gsx$firstname_3']['$t'].' '.$p['gsx$lastname_3']['$t']])){
			array_push($ret, array(
				'place' => '',
				'sender' =>	$p['gsx$firstname_3']['$t'].' '.$p['gsx$lastname_3']['$t'],
				'number' => '',
				'message' => 'Survivor',
				'from' => $p['id']['$t']
			));
			$tmp[$p['gsx$firstname_3']['$t'].' '.$p['gsx$lastname_3']['$t']] = true;
			}

			if(strpos(strtolower($p['gsx$firstname_4']['$t'].' '.$p['gsx$lastname_4']['$t']), strtolower($searchString))!== false)
			if(!isset($tmp[$p['gsx$firstname_4']['$t'].' '.$p['gsx$lastname_4']['$t']])){
			array_push($ret, array(
				'place' => '',
				'sender' =>	$p['gsx$firstname_4']['$t'].' '.$p['gsx$lastname_4']['$t'],
				'number' => '',
				'message' => 'Survivor',
				'from' => $p['id']['$t']
			));
			$tmp[$p['gsx$firstname_4']['$t'].' '.$p['gsx$lastname_4']['$t']] = true;
			}
		}

		}



		$tmp = array();

		$url = 'http://graph.facebook.com/666212400066213/photos?fields=name,tags,link&limit=1000';
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
				$p['name'] = preg_replace("/  /", " ", preg_replace("/(\r\n|\r|\n)/", " ", $p['name'])) . $name;
				if(strpos(strtolower($p['name']), strtolower($searchString)) !== false)
				array_push($ret, array(
					'place' => '',
					'sender' => $p['name'],
					'number' => '',
					'message' => strstr($p['name'], 'Help'),
					'from' => $p['link']
				));
		}

		}



		$url = 'https://www.google.org/personfinder/2013-yolanda/api/search?key=smo7n6_B3sgRMD9Y&q='.$searchString;
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
				array_push($ret, array(
					'place' => $p['home_street']. ' : '. $p['home_state'],
					'sender' => $p['full_name'] .' ' .$p['alternate_names'],
					'number' => '',
					'message' => preg_replace("/(\r\n|\r|\n)/","", $p['description'] . $mess),
					'from' => $p['source_url']
				));	
			}
		}
		
		$this->response(array('status'=>'Success','data'=>$ret),200);

	}
}

?>

