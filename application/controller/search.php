<?php
class Search extends Kiel_Controller
{
	public function index_get(){
		$required = array('query','source');
		$this->required_fields($required,$this->get_args);
		$request = urldecode($this->get_args['source']);
		$searchString = urldecode($this->get_args['query']);


		$data = array();
		
		$response = file_get_contents('http://graph.facebook.com/666212400066213/photos?fields=name,tags,link,source&limit=1000');
		$fb = array();
		$array = json_decode($response, true);
		foreach($array['data'] as $p){
			$name = "";	
			if(isset($p['tags'])) {
				foreach($p['tags']['data'] as $q){
					$name .= $q['name'].' & ';
				}
			}
			$p['name'] = preg_replace("/  /", " ", preg_replace("/(\r\n|\r|\n)/", " ", $p['name'])) .' '.$name;
			if(strpos(strtolower($p['name']), strtolower($searchString)) !== false){
				$fb[] = $p;
			}
		}

		$data['fb'] = $fb;

		$response = @file_get_contents('https://www.google.org/personfinder/2013-yolanda/api/search?key=smo7n6_B3sgRMD9Y&q='.urlencode($searchString));
		$temp = preg_replace("/pfif\:/", "", $response);
		$xml = simplexml_load_string($temp);
		$json = json_encode($xml);
		$array = json_decode($json,TRUE);
		$google = array();
		foreach($array['person'] as $p){
			$google[] = $p;
		}
		$data['google'] = $google;


		
		$dswd = array();
		$url = 'http://cors.io/spreadsheets.google.com/feeds/list/0ApSfq4LnrdaRdHBTSllLTVBaSW9UTjlobUZCNXRNN1E/od6/public/values?alt=json&q='.urlencode($searchString);
		$response = @file_get_contents($url);
		if($response){
			$array = json_decode($response, true);
			
			if(isset($array['feed']['entry'])){
				$temp = $array['feed']['entry'];
				foreach($temp as $p){

					if(strpos(strtolower($p['gsx$firstname']['$t'].' '.$p['gsx$lastname']['$t']), strtolower($searchString))!== false)
					if(!isset($tmp[$p['gsx$firstname']['$t'].' '.$p['gsx$lastname']['$t']])){
						array_push($dswd, array(
							'place' => '',
							'sender' =>	$p['gsx$firstname']['$t'].' '.$p['gsx$lastname']['$t'],
							'number' => '',
							'message' => 'Survivor',
							'from' => $p['id']['$t'],
						));
						$tmp[$p['gsx$firstname']['$t'].' '.$p['gsx$lastname']['$t']] = true;
					}

					if(strpos(strtolower($p['gsx$firstname_2']['$t'].' '.$p['gsx$lastname_2']['$t']), strtolower($searchString))!== false)
					if(!isset($tmp[$p['gsx$firstname_2']['$t'].' '.$p['gsx$lastname_2']['$t']])){
					array_push($dswd, array(
						'place' => '',
						'sender' =>	$p['gsx$firstname_2']['$t'].' '.$p['gsx$lastname_2']['$t'],
						'number' => '',
						'message' => 'Survivor',
						'from' => $p['id']['$t'],
					));
					$tmp[$p['gsx$firstname_2']['$t'].' '.$p['gsx$lastname_2']['$t']] = true;
					}

					if(strpos(strtolower($p['gsx$firstname_3']['$t'].' '.$p['gsx$lastname_3']['$t']), strtolower($searchString))!== false)
					if(!isset($tmp[$p['gsx$firstname_3']['$t'].' '.$p['gsx$lastname_3']['$t']])){
					array_push($dswd, array(
						'place' => '',
						'sender' =>	$p['gsx$firstname_3']['$t'].' '.$p['gsx$lastname_3']['$t'],
						'number' => '',
						'message' => 'Survivor',
						'from' => $p['id']['$t'],
					));
					$tmp[$p['gsx$firstname_3']['$t'].' '.$p['gsx$lastname_3']['$t']] = true;
					}

					if(strpos(strtolower($p['gsx$firstname_4']['$t'].' '.$p['gsx$lastname_4']['$t']), strtolower($searchString))!== false)
					if(!isset($tmp[$p['gsx$firstname_4']['$t'].' '.$p['gsx$lastname_4']['$t']])){
					array_push($dswd, array(
						'place' => '',
						'sender' =>	$p['gsx$firstname_4']['$t'].' '.$p['gsx$lastname_4']['$t'],
						'number' => '',
						'message' => 'Survivor',
						'from' => $p['id']['$t'],
					));
					$tmp[$p['gsx$firstname_4']['$t'].' '.$p['gsx$lastname_4']['$t']] = true;
					}
				}
			}
		}
		$data['dswd'] = $dswd;

		
		$url = 'http://reliefboard.com/search?loc=1&name=1&message=1&query='.urlencode($searchString).'&offset=0&limit=1000000000';
		$response = @file_get_contents($url);
		$relief = array();
		if($response){
			$json = stripslashes($response);
			$array = json_decode($json, true);
			if($array['data']['result_count'] > 0){
			$temp = $array['data']['result'];
				foreach($temp as $p){
					$relief[] = array(
						'place' => $p['place_tag'],
						'sender' => $p['sender'],
						'number' => $p['sender_number'],
						'message' => $p['message'],
						'from' => $url,
					);
				}
			}
		}
		
		
		$data['relief'] = $relief;
		
		
		$bangon = array();
		$response = file_get_contents('http://api.bangonph.com/v1/posts');
		if($response){
			$array = json_decode($response, TRUE);
			$temp = $array['data']['posts'];
			foreach($temp as $p){
				if(strpos(strtolower($p['location'].$p['name'].$p['message']), strtolower($searchString))) {
					$bangon[] = array(
						'place' => $p['location'],
						'sender' => $p['name'],
						'number' => $p['phone'],
						'message' => $p['message'],
						'from' => $url,
					);
				}
			}
		}
	
		$data['bangon'] = $bangon;

		if($request == 'mobile'){			// return 3 E
			$arr = array_slice($ret,0,3);
			$arr['count'] = count($arr);

			// DO MOBILE HERE
			$this->response(array('status'=>'Success','data'=>$arr),200);
		} else if($request == 'subscribe'){	//return 5 E
			$arr = array_slice($ret,0,5);
			$arr['count'] = count($arr);

			// DO MOBILE HERE
			$this->response(array('status'=>'Success','data'=>$arr),200);
		} else {
			$this->response(array('status'=>'Success','data'=>$data),200);
		}

	}
}

?>

