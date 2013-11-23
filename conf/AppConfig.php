<?php
	defined('AppAUTH') or die;

	/*
 	 * Will allow the use of oauth enabled requests
 	 *
 	 * Default value: TRUE
  	 */
	$config['oauth'] = TRUE;

	/*
 	 * Time in MINUTES on when the access token will expire
 	 *
 	 * Default value: TRUE
  	 */
	$config['oauth_access_token_expires'] = 86400;

	/*
	 *
	 * ===== OAUTH CONSTANTS ========
	 *
	 *	tables for oath required objects
	 *
	 *	CREATE TABLE oauth_clients (client_id VARCHAR(80) NOT NULL, client_secret VARCHAR(80) NOT NULL, redirect_uri VARCHAR(255) NOT NULL, PRIMARY KEY (client_id));
	 *	CREATE TABLE oauth_access_tokens (access_token VARCHAR(40) NOT NULL, client_id VARCHAR(80) NOT NULL, user_id VARCHAR(255), expires TIMESTAMP NOT NULL,  PRIMARY KEY (access_token));
	 *	CREATE TABLE oauth_request_tokens (request_token VARCHAR(40) NOT NULL, client_id VARCHAR(80) NOT NULL, user_id VARCHAR(255), expires TIMESTAMP NOT NULL,  PRIMARY KEY (request_token));
	 *	CREATE TABLE oauth_scopes (scope VARCHAR(255), access_token VARCHAR (80));
	 *	CREATE TABLE scopes (scope VARCHAR(255) NOT NULL, description TEXT, PRIMARY KEY(scope)); 
	 *  == ADD YOUR DESIRED FIELDS TO THE USER TABLE ==
	 *	CREATE TABLE oauth_users (username VARCHAR(255) NOT NULL, password VARCHAR(2000), first_name VARCHAR(255), last_name VARCHAR(255),email VARCHAR(255) NOT NULL, active boolean DEFAULT false, PRIMARY KEY (username));
	 */



	define('OAUTH_CLIENTS_TABLE','oauth_clients');
	define('OAUTH_ACCESS_TOKEN_TABLE','oauth_access_tokens');
	define('OAUTH_REQUEST_TOKEN_TABLE','oauth_request_tokens');
	define('OAUTH_USERS','oauth_users');
	define('OAUTH_SCOPES','oauth_scopes');




	
	/*
 	 * Compresses the response using gzip endcoding
 	 *
 	 * Default value: TRUE
  	 */
	$config['compress_output'] = TRUE;


	/*
 	 * Logging options
 	 * Logging is set to true by default
 	 * 
  	 */
	$config['enable_logging'] = TRUE;
	$config['logs_table'] = "logs";

	/*
	 * Default route/class that will be called
	 *
	 *
	 */
	$config['default_route'] = "";

	/*
	 * Sets the allowed method types for the rest server
	 * Accepts arrays of allowed method types
	 *
	 */
	$config['allowed_method_types'] = array('POST','GET','PUT','DELETE');

	$config['index_path_redirect'] = "kumusta";

	/*
	 * Sets the application folder path if web application is in the same folder as API
	 * 
	 *
	 */
	$config['application_path'] = "/kumusta";

	$config['load_db'] = TRUE;
?>
