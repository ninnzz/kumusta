<?php
class Voice_call extends Kiel_Controller
{

	public function call_in_post()
	{
		require('tropo_voice/tropo.class.php');

		$tropo = new Tropo();
		// $caller now has a hash containing the keys: id, name, channel, and network
		

		$tropo->ask('<speak>Please type the number code given to you, then press hash tag</speak>', array(
  			"choices"=>'[DIGITS]',
  			"name" => "confid", 
    		"attempts" => 5,
    		"timeout" => 60, 
    		"mode" => "dtmf",
    		"terminator" => "#",
  			"event"=> array(
    			'timeout' => 'Speak up!',
    		)
  		));

		$tropo->on(array("event" => "continue","next" => "/voice_call/match_ref/"));
		

		$tropo->renderJSON();

	}

	public function match_ref_post()
	{
		require('tropo_voice/tropo.class.php');
		$this->load_model('subscriber_model');


		$tropo = new Tropo();

		@$result = new Result();   
		$u_id = $result->getValue();	//gets the ref number


		$usr = $this->subscriber_model->subscriber_details($u_id);
		if($usr && $usr['result_count'] == '1'){
			$tropo->say('Redirecting your call');
			$num = $usr['result'][0]['phoneNumber'];
			$tropo->transfer(array($num,"sip:21581001@sip.tropo.net"),array('from'=>'21587625','terminator'=>'*',"playvalue" => "http://www.phono.com/audio/holdmusic.mp3"));
			
			$message = "Thanks for using this service. We also accept donations (5pesos at most) :) You can donate if you want by sending DONATE to 21587625";
			$url = "http://ec2-184-169-205-217.us-west-1.compute.amazonaws.com/callback.php";
			$fields_string = "message={$message}&phone_number=".$num;

			$ch = curl_init();

			//set the url, number of POST vars, POST data
			curl_setopt($ch,CURLOPT_URL, $url);
			curl_setopt($ch,CURLOPT_POST, 2);
			curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);

			//execute post
			$result = curl_exec($ch);

			//close connection
			curl_close($ch);			
		} else {
			$tropo->say('Invalid Reference Code. Ending call now!');
		}


		$tropo->RenderJson();
	}

	public function test_get()
	{
		$this->load_model('subscriber_model');
		$usr = $this->subscriber_model->subscriber_details('7');
		print_r($usr);
		
	}

}

?>

