<?php
	define('AppAUTH', 1);
	require_once('conf/AppConfig.php');
	require_once('conf/DBConfig.php');

	$object_name =null;	
	$method_name =null;	

	/*
     * Obtains the method and checks it with the allowede method in the configuration
     * Set the allowed method types in the AppConfig.php
	 */
	$method = trim(strtoupper($_SERVER['REQUEST_METHOD']));
	if( !in_array($method, $config['allowed_method_types']) ){
		header("HTTP/1.0 500 Internal Server Error");
		throw new Exception("Unsupported method type: '{$method}'", 1);		
	}

	/*
	 *	Checks if there are any path info /object/method
	 *
	 *
	 */
	if(!isset($_SERVER['PATH_INFO'])){
		if($config['index_path_redirect'] != ""){
			header("location:".$config['index_path_redirect']);
			exit();
		} else{
			header("HTTP/1.0 404 Page Not Found");
			throw new Exception("The page you are requesting does not exist", 1);
		}
	}
	


	$params = explode('/',trim($_SERVER['PATH_INFO'],'/'));

	$object_name = $params[0];
	$method_name = 'index_'.strtolower($method);
	
	if(count($params)>1 && isset($params[1])){
		$method_name = $params[1].'_'.strtolower($method);
	}

	if(file_exists("./application/controller/{$object_name}.php")){
		require_once("Kiel_Controller.php");
		require_once("./application/controller/{$object_name}.php");
	} else{
		$method_name = null;
		header("HTTP/1.0 404 Page Not Found");
		throw new Exception("Unknown object", 1);
	}

	$object_name = ucfirst(strtolower($object_name));
	if(class_exists($object_name)){
		if(method_exists($object_name, $method_name)){
			/*
			 * Instantiates the class object 
			 *
			 */
			$activeClass = new $object_name();
			/*==== Config setup =====*/
				if($config['load_db']){

					if(file_exists("./db_drivers/".$db_config['driver'].".php")){
						
						if(file_exists("./core/interface/data_handler.php")){
							require_once("./core/interface/data_handler.php");
						} else{
							header("HTTP/1.0 500 Internal Server Error");
							throw new Exception("DB Error", 1);				
						}
						
						require_once("./db_drivers/".$db_config['driver'].".php");
						$db = new db_handler($db_config['host'],$db_config['username'],$db_config['password'],$db_config['name']);
						$activeClass->setDataHandler($db);
					} else{
						header("HTTP/1.0 500 Internal Server Error");
						throw new Exception("DB Error", 1);				
					}
				} else {
					$method_name = null;
					header("HTTP/1.0 404 Page Not Found");
					throw new Exception("Cannot Load DB", 1);				
				}
			/*==== Config setup end =====*/

			/*
			 * Actual call to the function
			 * Filters the data first using getRequestData
			 *
			 */
			$activeClass->getRequestData($method);
			$response_data = $activeClass->$method_name();

		} else{
			$method_name = null;
			header("HTTP/1.0 404 Page Not Found");
			throw new Exception("Unknown method", 1);				
		}
	} else{
		$method_name = 'index';
		header("HTTP/1.0 404 Page Not Found");
		throw new Exception("Unknown object", 1);	
	}

?>
