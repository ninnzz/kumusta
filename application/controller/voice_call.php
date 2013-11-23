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


		$whisper = array();
		$choices = new Choices("1", "dtmf");
		$a = new Ask(1,true, $choices, NULL,"foo", true, "Press one to accept the call or any other number to decline.", 30, NULL, NULL, NULL, NULL, NULL, .01, NULL);
		$ask = array("ask" => $a);
		array_push($whisper, $ask);

		$say = array("say" => new Say("You are now being connected to the call."));
		array_push($whisper, $say);
		$on = array("event" => "connect", "whisper" => $whisper); 

		$tropo->transfer('+639152829238',array('from'=>"9875",'on'=>$on));
		$tropo->RenderJson();
	}

	public function test_get()
	{
		
	}

}

?>

