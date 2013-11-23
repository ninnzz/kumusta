<?php

	/**
	* Class Kiel_Controller
	*
	*/
	defined('AppAUTH') or die;
	
	class Kiel_Controller
	{
		protected $method;
		protected $firing_method;
		protected $user;
		protected $data_handler;
		protected $_zlib_oc = FALSE;

		protected $post_args = array();
		protected $delete_args = array();
		protected $get_args = array();
		protected $put_args = array();
		
		protected $selected = array();


		public function __construct()
		{	
			$this->_zlib_oc = @ini_get('zlib.output_compression');
		}

		public function load_model($model_name)
		{
			if(file_exists("./application/model/{$model_name}.php")){
				require_once("Kiel_Model.php");
				require_once("./application/model/{$model_name}.php");
				
				if(class_exists(ucfirst($model_name))){
					$mn = ucfirst($model_name);
					$this->$model_name = new $mn();
					
					$this->$model_name->setDataHandler($this->data_handler); 
				} else{
					header("HTTP/1.0 500 Internal Server Error");
					throw new Exception("Unknown Model: {$model_name}", 1);			
				}
			} else{
				header("HTTP/1.0 404 Page Not Found");
				throw new Exception("Unknown Model: {$model_name}", 1);
			}
		}

		public function required_fields($required,$fields)
		{
			foreach($required as $req){
				if(!isset($fields[$req])){
					header("HTTP/1.0 500 Internal Server Error");
					throw new Exception("The following fields are required: {$req}", 1);			
				}
			}
		}

		public function checkAuth($access_token)
		{
			$this->load_model('auth_model');
			$res = $this->auth_model->check_access($access_token);
			if($res['result_count'] !== 1){
				header("HTTP/1.0 500 Internal Server Error");
				throw new Exception("Authentication failed. Invalid APP_ID", 1);
			}
		}

		public function setDataHandler($db_connector)
		{
			$this->data_handler = $db_connector;
		}

		public function getRequestData($method)
		{
			$this->method = $method;
			
			switch ($method) {
				case 'GET':
					$this->xfClean($_GET);
				break;

				case 'POST':
					$this->xfClean($_POST);
				break;
				case 'PUT':
					parse_str(file_get_contents("php://input"),$_PUT);
					$this->xfClean($_PUT);
				break;
				case 'DELETE':
					parse_str(file_get_contents("php://input"),$_DELETE);
					$this->xfClean($_DELETE);
				break;
				default:
					header("HTTP/1.0 500 Internal Server Error");
					throw new Exception("Invalid method", 1);
					break;
			}

		}

		/**
		* Cleans the data input and prevents xss
		*/

		private function xfClean($args)
		{
			$tmp = array();
			foreach ($args as $key => $value){
			    $tmp[$key] =  strip_tags(filter_var($value,FILTER_SANITIZE_ENCODED));
				array_push($this->selected,$key);
			}

			switch (strtolower($this->method)) {
				case 'get':
					$this->get_args = $tmp;
					break;
				case 'post':
					$this->post_args = $tmp;
					break;
				case 'delete':
					$this->delete_args = $tmp;
					break;
				case 'put':
					$this->put_args = $tmp;
					break;
			}
		}

		public function response($data = array(), $http_code = 200)
		{

			global $start;
			global $config;
			if(ENVIRONMENT === 'development'){
				$data['method']			= $this->method;
				$data['memory_usage']	= ( ! function_exists('memory_get_usage')) ? '0' : round(memory_get_usage()/1024/1024, 2).'MB';
				
				if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
					$data['ellapsed_time'] = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']; 
				} else {
					$data['ellapsed_time'] = microtime(true) - $start ;
				}
			
			}

			// Is compression requested?
			if($config['compress_output']
				&& $this->_zlib_oc == FALSE
				&& extension_loaded('zlib')
				&& isset($_SERVER['HTTP_ACCEPT_ENCODING'])
				&& strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE)
			{
				// ob_start('ob_gzhandler');
			}
			$data['compress_output'] = $config['compress_output'];
			$output = json_encode($data);

			//make format dynamic later
			header('Content-Type: application/json');
			

			header('HTTP/1.1: ' . $http_code);
			header('Status: ' . $http_code);
			header('Access-Control-Allow-Origin: *');
			header('Access-Control-Allow-Methods: OPTIONS, DELETE, PUT');
			header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

			// If zlib.output_compression is enabled it will compress the output,
			// but it will not modify the content-length header to compensate for
			// the reduction, causing the browser to hang waiting for more data.
			// We'll just skip content-length in those cases.
			if ( ! $this->_zlib_oc && ! $config['compress_output'])
			{
				header('Content-Length: ' . strlen($output));
			}
			exit($output);
		}
	}
?>
