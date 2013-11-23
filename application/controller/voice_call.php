<?php
class Voice_call extends Kiel_Controller
{

	public function call_in_post()
	{
		require('tropo_voice/tropo.class.php');

		$tropo = new Tropo();
		// $caller now has a hash containing the keys: id, name, channel, and network
		

		$tropo->ask('Please say the four digit combination', array(
  			"choices"=>'[ANY]',
  			"name" => "confid", 
    		"attempts" => 5,
    		"timeout" => 60, 
    		"mode" => "speech",
  			"event"=> array(
    			'timeout' => 'Speak up!',
    		)
  		));

		$tropo->on(array("event" => "continue",'say'=> 'Fantastic! I love that, too!'));
		

		$tropo->renderJSON();

	}

	public function match_ref_post()
	{
		require('tropo_voice/tropo.class.php');


		$tropo = new Tropo();

		$result = new Result();   
		$conference = $result->getValue();
		$tropo->say('<speak>Conference ID <say-as interpret-as=\'vxml:digits\'>' . $conference . '</say-as> accepted.</speak>');
		$tropo->RenderJson();
	}

	public function test_get()
	{
		
	}

}

?>

