<?php
class Comment_model extends Kiel_Model{

	public function get_comments($parent_id = null, $offset = 0, $limit = 10){

		$where = array('parent_id'=>$parent_id);  

		$added = " ,(select logo from applications where applications.id = messages.source) as logo, (select name from applications where applications.id = messages.source) as app_name ";
		return $this->data_handler->get_where('messages',null,$where,$offset,$limit,null,'date_created',$added);

	}
}

?>