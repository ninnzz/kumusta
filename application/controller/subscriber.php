<?php
class Subscriber extends Kiel_Controller
{
	public function index_get()
	{
		$this->load_model('subscriber_model');
		
		$res  = $this->subscriber_model->get_queries();
		$this->response(array('status'=>'Success','data'=>$res),200);

	}
}

?>

