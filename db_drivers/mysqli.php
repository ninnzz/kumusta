<?php
	class db_handler implements data_handler{
		private $host; 
		private $username; 
		private $password; 
		private $db_name;
		private $query; 
		
		function __construct($h,$uname,$pass,$db_name) {
       		$this->host = $h;
       		$this->username = $uname;
       		$this->password = $pass;
       		$this->db_name = $db_name;
   		}


   		private function extract_column($data)
   		{	
   			$str = "";
   			foreach ($data as $d) {
   				$str .= $d.' ,';
   			}
   			return rtrim($str,',');
   		}

   		private function get_update_clause($data){
   			$str = '';
   			foreach ($data as $key => $value) {
   				
   				if(gettype($value) === "string"){
   					$value = "'{$value}'";
   				}
				$str .= "  ".$key." = ".$value." ,";
	   			
   			}
   			return rtrim($str, ',');
   		}

   		private function get_where_clause($data)
   		{
   			$str = "WHERE ";
   			foreach ($data as $key => $value) {
   				$p = ltrim($key,'!');
   				if(gettype($value) === "NULL"){
	   				if($p === $key){
		   				$str .= "  ".$p." is NULL AND";
		   			} else {
		   				$str .= "  ".$p." is not NULL AND";
		   			}
		   			continue;
   				}
   				if(gettype($value) === "string"){
   					$value = "'{$value}'";
   				}

   				if($p === $key){
	   				$str .= "  ".$p." = ".$value." AND";
	   			} else {
	   				$str .= "  ".$p." != ".$value." AND";
	   			}
   			}
   			return rtrim($str, 'AND');
   		}

		public function query($query)
		{
			$row_count = 0;
			$res = array();

			$link = mysqli_connect($this->host,$this->username ,$this->password,$this->db_name) or die('Database Connection Error');

			if($link->connect_errno > 0){
				$err = $link->connect_error;
				$link->close() or die('no links to close');
				header("HTTP/1.0 500 Internal Server Error");
    			throw new Exception("Database Connection Error [" . $err . "]", 1);
			}
			$link->autocommit(FALSE);
	
			if(!$result = $link->query($query)){
				$err = $link->error;
				$link->close();
 				header("HTTP/1.0 500 Internal Server Error");
    			throw new Exception("Database Connection Error [" . $err . "]", 1);
			}

			while($row = $result->fetch_assoc()){
  		 		array_push($res, $row);
			}
			$cnt = $result->num_rows;

			$result->free();
			$link->commit();
			$link->close() or die('no links to close');
			return(array('result' => $res, 'result_count'=>$cnt));

		}

		public function get($table=NULL,$data=NULL,$offset=0,$limit=10,$sort=NULL,$order=NULL)
		{
			$row_count = 0;
			$res = array();

			if(!$table){
				header("HTTP/1.0 500 Internal Server Error");
    			throw new Exception("Database Error :: Unknown table", 1);
			}
			$data = $data?$this->extract_column($data):' * ';


			$link = mysqli_connect($this->host,$this->username ,$this->password,$this->db_name) or die('Database Connection Error');

			if($link->connect_errno > 0){
				$err = $link->connect_error;
				$link->close() or die('no links to close');
				header("HTTP/1.0 500 Internal Server Error");
    			throw new Exception("Database Connection Error [" . $err . "]", 1);
			}
			$link->autocommit(FALSE);
	
			$query_message = "SELECT {$data} FROM {$table} ";

			if($order != NULL){
				$query_message .= "ORDER BY {$order} desc ";
			}

			if($offset != NULL){
				$query_message .= "LIMIT {$offset}, {$limit}";
			}

			$query_message .= ';';

			if(!$result = $link->query($query_message)){
				$err = $link->error;
				$link->close();
 				header("HTTP/1.0 500 Internal Server Error");
    			throw new Exception("Database Connection Error [" . $err . "] query=>({$query_message})", 1);
			}

			while($row = $result->fetch_assoc()){
  		 		array_push($res, $row);
			}
			$cnt = $result->num_rows;

			$result->free();
			$link->commit();
			$link->close() or die('no links to close');
			return(array('result' => $res, 'result_count'=>$cnt));
		}

		public function get_where($table=NULL,$data=NULL,$where=NULL,$offset=0,$limit=10,$sort=NULL,$order=NULL,$added='')
		{

			$row_count = 0;
			$res = array();

			if(!$table){
				header("HTTP/1.0 500 Internal Server Error");
    			throw new Exception("Database Error :: Unknown table", 1);
			}
			$data = $data?$this->extract_column($data):' * ';

			if($where && gettype($where) === 'array' && count($where) != 0){
				$where = $this->get_where_clause($where);
			} else {
				header("HTTP/1.0 500 Internal Server Error");
    			throw new Exception("Database Error :: Invalid where clause", 1);
			}


			$link = mysqli_connect($this->host,$this->username ,$this->password,$this->db_name) or die('Database Connection Error');

			if($link->connect_errno > 0){
				$err = $link->connect_error;
				$link->close() or die('no links to close');
				header("HTTP/1.0 500 Internal Server Error");
    			throw new Exception("Database Connection Error [" . $err . "]", 1);
			}
			$link->autocommit(FALSE);
	
			$query_message = "SELECT {$data} {$added} FROM {$table} {$where}";


			if($order != NULL){
				$query_message .= "ORDER BY {$order} desc ";
			}

			if($offset !== NULL){
				$query_message .= " LIMIT {$offset}, {$limit} ";
			}



			if(!$result = $link->query($query_message)){
				$err = $link->error;
				$link->close();
 				header("HTTP/1.0 500 Internal Server Error");
    			throw new Exception("Database Connection Error [" . $err . "] query = ({$query_message})", 1);
			}

			while($row = $result->fetch_assoc()){
  		 		array_push($res, $row);
			}
			$cnt = $result->num_rows;

			$result->free();
			$link->commit();
			$link->close() or die('no links to close');
			return(array('result' => $res, 'result_count'=>$cnt));
		}

		public function insert($table=NULL,$data=NULL)
		{
			$query_message = '';
			$row_count = 0;
			$res = array();

			if(!$table){
				header("HTTP/1.0 500 Internal Server Error");
    			throw new Exception("Database Error :: Unknown table", 1);
			}
			if(!$data){
				header("HTTP/1.0 500 Internal Server Error");
    			throw new Exception("Database Error :: No data to insert", 1);	
			}

			// $link = mysqli_connect(DBConfig::DB_HOST, DBConfig::DB_USERNAME, DBConfig::DB_PASSWORD, DBConfig::DB_NAME) or die('Database Connection Error');
			$link = mysqli_connect($this->host,$this->username ,$this->password,$this->db_name) or die('Database Connection Error');
			if($link->connect_errno > 0){
    			$err = $link->connect_error;
				$link->close() or die('no links to close');
 				header("HTTP/1.0 500 Internal Server Error");
    			throw new Exception("Database Connection Error [" . $err . "]", 1);
			}
			$link->autocommit(FALSE);

			$query_message = "INSERT into {$table} values({$data});";			

			if(!$result = $link->query($query_message)){
				$err = $link->error;
				$errNo = $link->errno;
				$affected = $link->affected_rows;
				$link->close();
 				return array('errcode'=>$errNo ,'error'=>$err,'affected_rows'=>$affected);
			}
			$res['affected_rows'] = $link->affected_rows;
			//$res['query_message'] = $query_message;
			
			$link->commit();
			$link->close() or die('no links to close');
			return($res);

		}

		public function delete($table,$where)
		{

		}

		public function update($table,$data)
		{

		}

		public function update_where($table=NULL,$data=NULL,$where=NULL)
		{

			$query_message = '';
			$row_count = 0;
		
			if(!$table){
				header("HTTP/1.0 500 Internal Server Error");
    			throw new Exception("Database Error :: Unknown table", 1);
			}
			if(!$data || !is_array($data) || count($data) == 0){
				header("HTTP/1.0 500 Internal Server Error");
    			throw new Exception("Database Error :: Invalid Dataset", 1);	
			}
			if($where && gettype($where) === 'array' && count($where) != 0){
				$where = $this->get_where_clause($where);
			} else {
				header("HTTP/1.0 500 Internal Server Error");
    			throw new Exception("Database Error :: Invalid where clause", 1);
			}

			$link = mysqli_connect($this->host,$this->username ,$this->password,$this->db_name) or die('Database Connection Error');
			if($link->connect_errno > 0){
    			$err = $link->connect_error;
				$link->close() or die('no links to close');
 				header("HTTP/1.0 500 Internal Server Error");
    			throw new Exception("Database Connection Error [" . $err . "]", 1);
			}
			$link->autocommit(FALSE);

			$data = $this->get_update_clause($data);
			
			$query_message = "UPDATE {$table} SET {$data} {$where};";
			

			if(!$result = $link->query($query_message)){
				$err = $link->error;
				$errNo = $link->errno;
				$affected = $link->affected_rows;
				$link->close();
 				return array('errcode'=>$errNo ,'error'=>$err,'affected_rows'=>$affected);
			}
			$res['affected_rows'] = $link->affected_rows;
			//$res['query'] = $query_message;
			
			$link->commit();
			$link->close() or die('no links to close');
			return($res);
		}

		public function update_batch($table, $data=array(),$where=array())
		{

		}

		public function insert_batch($table, $data)
		{

		}







	
	}
?>