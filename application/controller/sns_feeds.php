<?php
class Sns_feeds extends Kiel_Controller{

	public function feed_get()
	{
		$this->load_model('sns_feeds_model');
		$res = $this->sns_feeds_model->list_all();
		$this->response(array('status'=>'Success','data'=>$res),200);
	}
}

?>

