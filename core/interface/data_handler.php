<?php
	defined('AppAUTH') or die;
	

	interface data_handler
	{	
		
		/**
		* Allows execution of custom query
		* @param query - string/query string
		* @return false| object
		*/
		public function query($query);
		
		/**
		* Fetches data from the data source
		* @param table(string) - table/object name
		* @param data(array) - data to be fetched
		* @param offset(int) - offset
		* @param limit(int) - limit per query
		* @param sort - sort field
		* @param order - sort order
		* @return false| object
		*/
		public function get($table,$data,$offset,$limit,$sort,$order);
		
		/**
		* Fetches data from the data source
		* @param table(string) - table/object name
		* @param data(array) - data to be fetched
		* @param where(array) - fields to be compared
		* @param offset(int) - offset
		* @param limit(int) - limit per query
		* @param sort - sort field
		* @param order - sort order
		* @return false| object
		*/
		public function get_where($table,$data,$where,$offset,$limit,$sort,$order);
		
		/**
		* Fetches data from the data source
		* @param table(string) - table/object name
		* @param data(array) - data to be inserted
		* @return false| object
		*/
		public function insert($table,$data);
		
		/**
		* Deletes data from the data source
		* @param table(string) - table/object name
		* @param where(array) - fields to be compared
		* @return false| object
		*/
		public function delete($table,$where);

		/**
		* updates data from the data source
		* @param table(string) - table/object name
		* @param data(array) - data to be updated
		* @return false| object
		*/
		public function update($table,$data);

		/**
		* Updates data from the data source
		* @param table(string) - table/object name
		* @param data(array) - data to be updated
		* @param where(array) - fields to be compared
		* @return false| object
		*/
		public function update_where($table,$data,$where);


		/**
		* To be followed
		*/
		public function update_batch($table, $data=array(),$where=array());
		public function insert_batch($table, $data);
	}

?>