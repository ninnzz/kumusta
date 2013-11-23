<?php
require_once('../lib/Phirehose.php');
require_once('../lib/UserstreamPhirehose.php');

/**
 * Barebones example of using UserstreamPhirehose.
 */
class MyUserConsumer extends UserstreamPhirehose 
{
  /**
   * First response looks like this:
   *    $data=array('friends'=>array(123,2334,9876));
   *
   * Each tweet of your friends looks like:
   *   [id] => 1011234124121
   *   [text] =>  (the tweet)
   *   [user] => array( the user who tweeted )
   *   [entities] => array ( urls, etc. )
   *
   * Every 30 seconds we get the keep-alive message, where $status is empty.
   *
   * When the user adds a friend we get one of these:
   *    [event] => follow
   *    [source] => Array(   my user   )
   *    [created_at] => Tue May 24 13:02:25 +0000 2011
   *    [target] => Array  (the user now being followed)
   *
   * @param string $status
   */
  public function enqueueStatus($status)
  {
    /*
     * In this simple example, we will just display to STDOUT rather than enqueue.
     * NOTE: You should NOT be processing tweets at this point in a real application, instead they
     *  should be being enqueued and processed asyncronously from the collection process. 
     */
    $data = json_decode($status, true);
    echo date("Y-m-d H:i:s (").strlen($status)."):".print_r($data,true)."\n";
  }

}

//These are the application key and secret
//You can create an application, and then get this info, from https://dev.twitter.com/apps
//(They are under OAuth Settings, called "Consumer key" and "Consumer secret")
define('TWITTER_CONSUMER_KEY', 'gydBQbbeBpnAoSHNiuAyhA');
define('TWITTER_CONSUMER_SECRET', 'rLMof1rKKMN4zSq94JM3hNszQeqSGouXZEyJavkrU');

//These are the user's token and secret
//You can get this from https://dev.twitter.com/apps, under the "Your access token"
//section for your app.
define('OAUTH_TOKEN', '185918986-zN4Ezdqi2944PIEDHEycQCvA3rrDpwmkV7XHlR5C');
define('OAUTH_SECRET', 'fjpgmaIvPda31aBRHQU6bmVQawdAvIZEfv7GVo59o');

// Start streaming
$sc = new MyUserConsumer(OAUTH_TOKEN, OAUTH_SECRET);
$sc->consume();
