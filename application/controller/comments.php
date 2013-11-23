<?php
class Comments extends Kiel_Controller
{
	public function index_get(){
		$required = array('parent_id');
		$this->required_fields($required,$this->get_args);

		$this->load_model('comment_model');


		$offset    = $this->get_args['offset'];
		$limit     = $this->get_args['limit'];
		$parent_id = isset($this->get_args['parent_id'])?$this->get_args['parent_id']:NULL;

		$res  = $this->comment_model->get_comments($parent_id ,$offset ,$limit);
		$this->response(array('status'=>'Success','data'=>$res),200);

	}
}

?>

