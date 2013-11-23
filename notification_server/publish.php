<?php   
 
//publish.php    
$redis = new Redis();    
$redis->pconnect('push.rboard.com',6378);
  $redis->publish('chan-1', 'hello, world!'); // send message to channel 1.
  $redis->publish('chan-2', 'hello, world2!'); // send message to channel 2.
 
  print "\n";
  $redis->close();
 
?>