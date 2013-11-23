<?php
class Voice_call extends Kiel_Controller
{

	public function call_in_post()
	{
		require('tropo_voice/tropo.class.php');

		$tropo = new Tropo();
		// $caller now has a hash containing the keys: id, name, channel, and network
		

		$tropo->ask('Please type the four digit combination, then press hash tag', array(
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


		$tropo = new Tropo();

		@$result = new Result();   
		$conference = $result->getValue();	//gets the ref number
		// $tropo->say('<speak>Conference ID <say-as interpret-as=\'vxml:digits\'>' . $conference . '</say-as> accepted.</speak>');
		$tropo->say('Redirecting your call');

		$tropo->transfer(array("9153203958","sip:21581001@sip.tropo.net"),array('terminator'=>'*',"playvalue" => "http://www.phono.com/audio/holdmusic.mp3",
));
		$tropo->RenderJson();
	}

	public function test_get()
	{
		
	}

}

?>

