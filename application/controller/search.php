<?php
class Search extends Kiel_Controller
{
	public function index_get(){
		$required = array('query','source');
		$this->required_fields($required,$this->get_args);
		$request = $this->get_args['source'];
		$searchString = $this->get_args['query'];

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
					strtolower('/('.preg_replace('[ \t\n]','|',urldecode($searchString)).')/'),
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
				));
				}
		}

		$ret['fb'] = $fb;


		}
		$url = 'https://www.google.org/personfinder/2013-yolanda/api/search?key=smo7n6_B3sgRMD9Y&q='.urlencode($searchString);
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


			array_push($google, array(
				'place' => "".$p['home_street'].' : '.$p['home_city']	.' : '. $p['home_state'],
				'sender' => "".$p['full_name'] .' ' .$p['alternate_names'].' : '.$p['given_name'].$p['family_name'],
				'number' => '',
				'message' => "".preg_replace("/(\r\n|\r|\n)/","", $p['description'] . $mess),
				'from' => "".$p['source_url'],
			));	
		}
		}

		
		$ret['google'] = $google;

		$tmp = array();
		$url = 'http://cors.io/spreadsheets.google.com/feeds/list/0ApSfq4LnrdaRdHBTSllLTVBaSW9UTjlobUZCNXRNN1E/od6/public/values?alt=json&q='.urlencode($searchString);
		$response = file_get_contents($url);
		if($response){
		$array = json_decode($response, true);
		$data = $array['feed']['entry'];
		foreach($data as $p){

			if(preg_match(strtolower('/('.strtolower(str_ireplace(' ','|',$p['gsx$firstname']['$t']).'|'. str_ireplace(' ','|',$p['gsx$lastname']['$t'])). ')/'), strtolower($searchString)))
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

			if(preg_match(strtolower('/('.strtolower(str_ireplace(' ','|',$p['gsx$firstname_2']['$t']).'|'. str_ireplace(' ','|',$p['gsx$lastname_2']['$t'])). ')/'), strtolower($searchString)))
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

			if(preg_match(strtolower('/('.strtolower(str_ireplace(' ','|',$p['gsx$firstname_3']['$t']).'|'. str_ireplace(' ','|',$p['gsx$lastname_3']['$t'])). ')/'), strtolower($searchString)))
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

			if(	preg_match(strtolower('/('.strtolower(str_ireplace(' ','|',$p['gsx$firstname_4']['$t']).'|'. str_ireplace(' ','|',$p['gsx$lastname_4']['$t'])). ')/'), strtolower($searchString)))
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


		$ret['dswd'] = $dswd;

		$url = 'http://reliefboard.com/search?loc=1&name=1&message=1&query='.urldecode($searchString).'&offset=0&limit=1000000000';
		$response = file_get_contents($url);
		if($response){
		$json = stripslashes($response);
		$array = json_decode($json, true);
		if($array['data']['result_count'] > 0){
		$data = $array['data']['result'];
		foreach($data as $p){
			array_push($relief, array(
				'id' => $p['id'],
				'place' => urldecode($p['place_tag']),
				'sender' => urldecode($p['sender']),
				'number' => $p['sender_number'],
				'message' => urldecode($p['message']),
				'from' => $url,
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
			if(preg_match(strtolower('/('.strtolower(str_ireplace(' ','|',$p['location'].' '.$p['name'].' '.$p['message'] )). ')/'), strtolower(urldecode($searchString))))
			if(strpos(strtolower($p['location'].$p['name'].$p['message']), strtolower($searchString))) {
				array_push($bangon, array(
					'id'	=> $p['id'],
					'place' => $p['location'],
					'sender' => $p['name'],
					'number' => $p['phone'],
					'message' => $p['message'],
					'from' => $url,
				));
			}
		}
		}
	
		$ret['bangon'] = $bangon;

		if($request == 'mobile'){			// return 3 E
			$arr = array_slice(array_merge($fb, $google, $dswd,$relief, $bangon),0,3);
			$arr['count'] = count($arr);

			// DO MOBILE HERE
			$this->response(array('status'=>'Success','data'=>$arr),200);
		} else if($request == 'subscibe'){	//return 5 E
			$arr = array_slice(array_merge($fb, $google, $dswd,$relief, $bangon),0,5);
			$arr['count'] = count($arr);

			// DO MOBILE HERE
			$this->response(array('status'=>'Success','data'=>$arr),200);
		} else {
			$this->response(array('status'=>'Success','data'=>$ret),200);
		}

	}
}

?>

