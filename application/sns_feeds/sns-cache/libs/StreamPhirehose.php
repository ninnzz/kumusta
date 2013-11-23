<?php

require_once "phirehose-master/lib/Phirehose.php";
require_once "phirehose-master/lib/UserstreamPhirehose.php";

class StreamPhirehose extends UserstreamPhirehose {
    private $_follows;

    public function setParams($follows) {
        $this->_follows = $follows;
    }

    public function enqueueStatus($status) { }

    protected function connect_oauth() {
        $connectFailures = 0;
        $tcpRetry = $this->tcpBackoff / 2;
        $httpRetry = $this->httpBackoff / 2;

        $this->log("Following IDs: " . $this->_follows);

        do {
            // Check filter predicates for every connect (for filter method)
            if ($this->method == self::METHOD_FILTER) {
                $this->checkFilterPredicates();
            }
      
            // Construct URL/HTTP bits
            $url = self::URL_BASE . $this->method . '.' . $this->format;
            $urlParts = parse_url($url);
            $authCredentials = base64_encode($this->username . ':' . $this->password);
      
            $requestParams = array('delimited' => 'length', 'follow' => $this->_follows);
      
            // Filter takes additional parameters
            //if ($this->method == self::METHOD_USER && count($this->trackWords) > 0) {
                //$requestParams['track'] = implode(',', $this->trackWords);
            //}
            if ($this->method == self::METHOD_USER && count($this->followIds) > 0) {
                $requestParams['follow'] = implode(',', $this->followIds);
            }
  
            $this->log('Connecting to twitter stream: ' . $url . ' with params: '
                . str_replace("\n", '', var_export($requestParams, TRUE)));
      
            /**
            * Open socket connection to make POST request. It'd be nice to use stream_context_create with the native
            * HTTP transport but it hides/abstracts too many required bits (like HTTP error responses).
            */
            $errNo = $errStr = NULL;
            $scheme = ($urlParts['scheme'] == 'https') ? 'ssl://' : 'tcp://';
            $port = ($urlParts['scheme'] == 'https') ? 443 : 80;
      
            /**
            * We must perform manual host resolution here as Twitter's IP regularly rotates (ie: DNS TTL of 60 seconds) and
            * PHP appears to cache it the result if in a long running process (as per Phirehose).
            */
            $streamIPs = gethostbynamel($urlParts['host']);
            if (empty($streamIPs)) {
                throw new PhirehoseNetworkException("Unable to resolve hostname: '" . $urlParts['host'] . '"');
            }
      
            // Choose one randomly (if more than one)
            $this->log('Resolved host ' . $urlParts['host'] . ' to ' . implode(', ', $streamIPs));
            $streamIP = $streamIPs[rand(0, (count($streamIPs) - 1))];
            $this->log('Connecting to ' . $streamIP);
      
            @$this->conn = fsockopen($scheme . $streamIP, $port, $errNo, $errStr, $this->connectTimeout);
  
            // No go - handle errors/backoff
            if (!$this->conn || !is_resource($this->conn)) {
                $this->lastErrorMsg = $errStr;
                $this->lastErrorNo = $errNo;
                $connectFailures ++;
                if ($connectFailures > $this->connectFailuresMax) {
                    $msg = 'TCP failure limit exceeded with ' . $connectFailures . ' failures. Last error: ' . $errStr;
                    $this->log($msg,'error');
                    throw new PhirehoseConnectLimitExceeded($msg, $errNo); // Throw an exception for other code to handle
                    }
                // Increase retry/backoff up to max
                $tcpRetry = ($tcpRetry < $this->tcpBackoffMax) ? $tcpRetry * 2 : $this->tcpBackoffMax;
                $this->log('TCP failure ' . $connectFailures . ' of ' . $this->connectFailuresMax . ' connecting to stream: ' .
                $errStr . ' (' . $errNo . '). Sleeping for ' . $tcpRetry . ' seconds.','info');
                sleep($tcpRetry);
                continue;
            }
      
            // TCP connect OK, clear last error (if present)
            $this->log('Connection established to ' . $streamIP);
            $this->lastErrorMsg = NULL;
            $this->lastErrorNo = NULL;
      
            // If we have a socket connection, we can attempt a HTTP request - Ensure blocking read for the moment
            stream_set_blocking($this->conn, 1);
  
            // Encode request data
            $postData = http_build_query($requestParams);
      
            // Oauth tokens
            $oauthHeader = $this->getOAuthHeader('POST', $url, $requestParams);
      
            // Do it
            fwrite($this->conn, "POST " . $urlParts['path'] . " HTTP/1.0\r\n");
            fwrite($this->conn, "Host: " . $urlParts['host'].':'.$port . "\r\n");
            fwrite($this->conn, "Content-type: application/x-www-form-urlencoded\r\n");
            fwrite($this->conn, "Content-length: " . strlen($postData) . "\r\n");
            fwrite($this->conn, 'User-Agent: ' . self::USER_AGENT . "\r\n");
            fwrite($this->conn, $oauthHeader."\r\n");
            fwrite($this->conn, "\r\n");
            fwrite($this->conn, $postData . "\r\n");
            fwrite($this->conn, "\r\n");

            $this->log("POST " . $urlParts['path'] . " HTTP/1.0");
            $this->log("Host: " . $urlParts['host'].':'.$port);
            $this->log("Content-type: application/x-www-form-urlencoded");
            $this->log("Content-length: " . strlen($postData));
            $this->log('User-Agent: ' . self::USER_AGENT);
            $this->log($oauthHeader);
            $this->log('');
            $this->log($postData);
            $this->log('');
      
            // First line is response
            list($httpVer, $httpCode, $httpMessage) = preg_split('/\s+/', trim(fgets($this->conn, 1024)), 3);
      
            // Response buffers
            $respHeaders = $respBody = '';

            // Consume each header response line until we get to body
            while ($hLine = trim(fgets($this->conn, 4096))) {
                $respHeaders .= $hLine;
            }
      
            // If we got a non-200 response, we need to backoff and retry
            if ($httpCode != 200) {
                $connectFailures ++;
        
                // Twitter will disconnect on error, but we want to consume the rest of the response body (which is useful)
                while ($bLine = trim(fgets($this->conn, 4096))) {
                    $respBody .= $bLine;
                }
        
                // Construct error
                $errStr = 'HTTP ERROR ' . $httpCode . ': ' . $httpMessage . ' (' . $respBody . ')';
        
                // Set last error state
                $this->lastErrorMsg = $errStr;
                $this->lastErrorNo = $httpCode;
        
                // Have we exceeded maximum failures?
                if ($connectFailures > $this->connectFailuresMax) {
                    $msg = 'Connection failure limit exceeded with ' . $connectFailures . ' failures. Last error: ' . $errStr;
                    $this->log($msg,'error');
                    throw new PhirehoseConnectLimitExceeded($msg, $httpCode); // We eventually throw an exception for other code to handle
                }
                // Increase retry/backoff up to max
                $httpRetry = ($httpRetry < $this->httpBackoffMax) ? $httpRetry * 2 : $this->httpBackoffMax;
                $this->log('HTTP failure ' . $connectFailures . ' of ' . $this->connectFailuresMax . ' connecting to stream: ' .
                    $errStr . '. Sleeping for ' . $httpRetry . ' seconds.','info');
                sleep($httpRetry);
                continue;
        
            } // End if not http 200
      
        // Loop until connected OK
        } while (!is_resource($this->conn) || $httpCode != 200);
    
        // Connected OK, reset connect failures
        $connectFailures = 0;
        $this->lastErrorMsg = NULL;
        $this->lastErrorNo = NULL;
    
        // Switch to non-blocking to consume the stream (important)
        stream_set_blocking($this->conn, 0);
    
        // Connect always causes the filterChanged status to be cleared
        $this->filterChanged = FALSE;
    
        // Flush stream buffer & (re)assign fdrPool (for reconnect)
        $this->fdrPool = array($this->conn);
        $this->buff = '';
  
    }

}

?>
