<?php
	defined('AppAUTH') or die;
	
	class Kiel_Model
	{

		/**
		* @var unix time stamp for the transacton
		*/
		protected $time;

		protected $table_name;

		protected $columns;
		
		protected $selectable_columns;

		protected $searchable_columns;

		protected $data_handler;

		function __construct(){

			$this->_time = time();
		}

		public function setDataHandler($db_connector)
		{
			$this->data_handler = $db_connector;
		}

		public function get_item()
		{
			
		}

		public function update_item()
		{
			
		}

		public function insert_item()
		{
			
		}

		public function delete_item()
		{

		}


	}
?>
