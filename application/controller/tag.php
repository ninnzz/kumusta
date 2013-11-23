<?php
class Tag extends Kiel_Controller
{
	/**
	 *	@return json_object - tags for a post
	**/
	public function index_get()
	{
		$required = array('post_id');
		$this->required_fields($required,$this->get_args);

		$this->load_model('tag_model');

		$res = $this->tag_model->get_tag($this->get_args['post_id']);

		if($res['result_count'] != 0){	
			if(trim($t) !== ""){
				$t = explode(',', urldecode($res['result'][0]['tags']));
				$res = (object)array('tag_count'=>count($t),'tags'=>$t);
			} else {
				$res = (object)array('tag_count'=>0,'tags'=>NULL);
			}
		} else {
			$res = (object)array('result_count'=>0,'message'=>"Cannot find post");
		}


		$this->response(array('status'=>'Success','data'=>$res),200);
		
	}



	public function index_put()
	{
		$required = array('tags','post_id','app_id');

		$this->required_fields($required,$this->put_args);
		$this->checkAuth($this->put_args['app_id']);
		
		$this->load_model('tag_model');
		$tag = explode(',',urldecode($this->put_args['tags']));
		$t = "";
		for($i=0;$i<count($tag);$i++){
			$t .= trim($tag[$i]).',';
		}

		$res = $this->tag_model->update_tag(rtrim($t,','),$this->put_args['post_id']);
		
		$this->response(array('status'=>'Success','data'=>$res),200);

	}

	public function all_get()
	{
		$this->load_model('tag_model');
		
	}


}

?>

