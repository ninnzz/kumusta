<?php
		$params['facebook_access_token'] = 'CAADDaNqhbVgBAHJqjx4fqE8iN006WvF9tBoJK9s7DWy5UAM4RMWyhiMGxQOyuMR32uYhZBrUlx42Jv9SOefXh2JA051xig8l2TAd5XymykksQD3ximfthOXl2CnSlY3KaqFDtbZBuz1WOFI3ZAVaY9U9FLiZCugYCUhVZBjzeJbRXeM2EIos9QXO0azcCE6EZD';
		//https://graph.facebook.com/oauth/access_token?client_id=214855112027480&client_secret=d481012df6d2e947e8442cc35d211fd3&grant_type=fb_exchange_token&fb_exchange_token=
		$params['twitter_access_token'] = '2190619520-lmj8aeP0mjXFWOH8feFGA144qaBPJMLjlbAy7kF';
		$params['twitter_access_secret'] = '2SO03jgYn31wJEZyXkaQI48MfX56Ktbo8fM7G2URiFfUB';
		$params['place'] = '454373604683875';
		$params['message'] = 'hihihihihi';
		//open connection
		$ch = curl_init();
		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL, 'http://api.buzzboarddev.stratpoint.com/posts/v1/fb_post');
		curl_setopt($ch,CURLOPT_POST, 3);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $params);

		//execute post
		curl_exec($ch);
		//close connection
		curl_close($ch);
?>
