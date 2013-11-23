<?php
class Subscriber_model extends Kiel_Model{

	public function get_queries(){
		return $this->data_handler->get('search',array('searchString'),null,null,null,'searchString');
	}


	public function subscriber_details_by_search($query)
	{
		$query = strtolower(trim($query));
		$get_queries = "select users.* from users, search where users.id = search.userId AND lower(searchString) ='{$query}';"
		return $this->data_handler->query($get_queries);
	}
	public function subscriber_details($id)
	{
		if(is_numeric($id)){
			$id = intval($id);
			return $this->data_handler->get_where('users',null,array('id'=>$id),null,null,null,'id','');
		} else {
			return false;
		}
	}
}

?>