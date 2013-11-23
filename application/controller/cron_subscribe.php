<?php
class Cron_subscribe extends Kiel_Controller
{

	public function index_get()
	{
		$required = array('app_id');
		$this->required_fields($required,$this->get_args);
		if($this->get_args['app_id'] != "90an729m.kumusta.web"){
			$this->response(array('status'=>'You dont have permission to access this URL'),500);
		}

		$this->load_model('subscriber_model');

		$res = $this->subscriber_model->all_subscribed();


		// put here the function for searching just loop through the result and send message
		//pag nakakita ka ng number, may tatawagin kang API..


		$this->response(array('status'=>'Success','data'=>$res),200);


	}

}

?>

