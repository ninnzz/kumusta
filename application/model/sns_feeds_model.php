<?php
class Sns_feeds_model extends Kiel_Model{

	public function list_all()
	{
		$query  = "SELECT * FROM sns_cache.parsed_contents GROUP BY post_id ORDER BY post_date DESC";
		return $this->data_handler->query($query);
	}
}

?>
