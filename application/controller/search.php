<?php
class Search extends Kiel_Controller
{

	public function index_get()
	{
		$this->load_model('feed_model');

		$required = array('loc','name','message','query');
		$data = $this->get_args;
		$this->required_fields($required,$data);

		$str = '';
		if($data['loc'] === "1"){
			$str .= " place_tag like '%{$data['query']}%' OR";
		}
		if($data['name'] === "1"){
			$str .= " sender like '%{$data['query']}%' OR";
		}
		if($data['message'] === "1"){
			$str .= " message like '%{$data['query']}%'";
		}

		$str = rtrim($str, 'OR');

		if($str != ''){
			$str = 'WHERE '.$str;
			$res = $this->feed_model->search_item($str, $data['offset'], $data['limit']);
		} else {
			$res = $this->feed_model->get_messages();
		}
		
		$this->response(array('status'=>'Success','data'=>$res),200);

	}

	public function google_finder_get()
	{
		$q = $this->get_args['query'];

		$key = "smo7n6_B3sgRMD9Y";
  
        $url = "https://www.google.org/personfinder/2013-yolanda/api/search?key={$key}&q={$q}";

        $response   = file_get_contents($url);
        $data = preg_replace("/pfif\:/", "", $response);
        
        $xml = simplexml_load_string($data);

        $json = json_encode($xml);
        $array = json_decode($json,TRUE);

//        if(count($array) > 0 && trim($array[0]) != "" ){
			$this->response(array('status'=>'Success','data'=>$array),200);
//		} else {
			// $this->response(array('status'=>'Success','data'=>'','query' => $url),200);
		// }
	}
}

?>

