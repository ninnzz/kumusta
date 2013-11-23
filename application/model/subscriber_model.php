<?php
class Subscriber_model extends Kiel_Model{

	public function get_queries(){

		return $this->data_handler->get('search',array('searchString'),null,null,null,'searchString');

	}
}

?>