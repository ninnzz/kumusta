<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  A CodeIgniter compatible wrapper for TwitterOAuth by Abraham Williams. 
 *  Includes a simple authentication method that allows easy validation from a 
 *  CodeIgniter controller. Requires the above class.
 *    
 *  @author Ben Sekulowicz-Barclay
 *  @link   http://www.beseku.com
 */

require_once('OAuth.php');
require_once('twitteroauth.php');

class Twitter {
    
    private $consumer_key;
    private $consumer_secret;
    
    private $oauth_token;
    private $oauth_token_secret;  
    private $oauth_verifier;
    
    /**
     *  @access public
     *  @return void
     *  @author Ben Sekulowicz-Barclay
     */

    public function __construct($params = array()) {
        // @DEBUG
        $CI =& get_instance();
        
        $CI->load->helper('array');
        $CI->load->helper('url');
        $CI->load->library('input');
        $CI->load->library('session');
        
        // Get the required keys/tokens/secrets from the params/session ...
        $this->consumer_key         = element('consumer_key', $params);
        $this->consumer_secret      = element('consumer_secret', $params);
        $this->oauth_token          = $CI->session->userdata('oauth_token');
        $this->oauth_token_secret   = $CI->session->userdata('oauth_token_secret');
        $this->oauth_verifier       = $CI->input->get('oauth_verifier');
        
        // Unset everything in the session ...          
        $CI->session->unset_userdata('oauth_token');
        $CI->session->unset_userdata('oauth_token_secret');
    }
    
    /**
     *  @access public
     *  @return void
     *  @author Ben Sekulowicz-Barclay
     */
     
    public function authenticate() {
        $CI =& get_instance();        
        $CI->load->library('input');
        $CI->load->library('session');
    
        // If we have oauth token/oauth token secret pair ... but need an access token ...
        if ($this->oauth_token && $this->oauth_token_secret && $this->oauth_verifier) {
            // Build our connection once to get our access key ...
            $twitter = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $this->oauth_token, $this->oauth_token_secret);
            $request = $twitter->getAccessToken($this->oauth_verifier);
            
            // Put everything in the class vars ...          
            $this->oauth_token = $request['oauth_token'];
            $this->oauth_token_secret = $request['oauth_token_secret'];
            
            // Build our connection again to get our full access ...
            $twitter = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $this->oauth_token, $this->oauth_token_secret);
            $request = $twitter->get('account/verify_credentials');
            
            // If we got authorisation from Twitter, return the account and OAuth details ...
            if ($twitter->http_code == 200) {
                $request->oauth_token = $this->oauth_token;
                $request->oauth_token_secret = $this->oauth_token_secret;
                
                return $request;
            }
        }
        
        // If we get this far, apply for a temporary pair and authorise with Twitter ...
        $twitter = new TwitterOAuth($this->consumer_key, $this->consumer_secret);
        $request = $twitter->getRequestToken(current_url());
        
        // Put everything in the session ...          
        $CI->session->set_userdata('oauth_token', $request['oauth_token']);
        $CI->session->set_userdata('oauth_token_secret', $request['oauth_token_secret']);
        
        header('Location: ' . $twitter->getAuthorizeURL($request['oauth_token']));
    }
    
    /**
     *  @access public
     *  @param  string
     *  @param  string
     *  @param  string
     *  @param  array
     *  @return mixed
     *  @author Ben Sekulowicz-Barclay
     */
     
    public function delete($oauth_token, $oauth_token_secret, $url, $parameters = array()) {        
        // Define our Twitter connection
        $twitter = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $oauth_token, $oauth_token_secret);
        
        // Return the twitter response ...
        return $twitter->delete($url, $parameters);
    }
    
    /**
     *  @access public
     *  @param  string
     *  @param  string
     *  @param  string
     *  @param  array
     *  @return mixed
     *  @author Ben Sekulowicz-Barclay
     */
     
    public function get($oauth_token, $oauth_token_secret, $url, $parameters = array()) {        
        // Define our Twitter connection
        $twitter = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $oauth_token, $oauth_token_secret);
        
        // Return the twitter response ...
        return $twitter->get($url, $parameters);
    }
    
    /**
     *  @access public
     *  @param  string
     *  @param  string
     *  @param  string
     *  @param  array
     *  @return mixed
     *  @author Ben Sekulowicz-Barclay
     */
     
    public function post($oauth_token, $oauth_token_secret, $url, $parameters = array()) {        
        // Define our Twitter connection
        $twitter = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $oauth_token, $oauth_token_secret);
        
        // Return the twitter response ...
        return $twitter->post($url, $parameters);
    }
}

/* End of file Twitter.php */
/* Location: ./application/libraries/Twitter.php */