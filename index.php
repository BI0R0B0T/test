<!DOCTYPE HTML>
<html>
<head>
	<title>Страница авторизации</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="autor" content="Dolgov M.S." />
</head>
<body>
<a href="http://oauth.vk.com/authorize?client_id=<?php
	$vk_client_id = 3022262;
	$vk_secret = "UzxZV1JkMWPvCyy9uW39";
	define("APP_ID",3022262);
 	echo $vk_client_id; 	
?>&scope=friends&redirect_uri=http://kodomo.fbb.msu.ru/~dolgov/piraty/graber.html&display=page&response_type=token" title="Зайти через ВКонтакте">Зайти через ВКонтакте</a>
<div><pre>
<?php
	var_dump($GLOBALS);
	if(isset($_GET["code"])){
		$code = $_GET["code"];
	}
	
function authOpenAPIMember() {
	$session = array();
	$member = FALSE;
	$valid_keys = array('expire', 'mid', 'secret', 'sid', 'sig');
	$app_cookie = $_COOKIE['vk_app_'.APP_ID];
	if ($app_cookie) {
		$session_data = explode ('&', $app_cookie, 10);
		foreach ($session_data as $pair) {
			list($key, $value) = explode('=', $pair, 2);
			if (empty($key) || empty($value) || !in_array($key, $valid_keys)) {
			continue;
			}
			$session[$key] = $value;
		}
		foreach ($valid_keys as $key) {
			if (!isset($session[$key])) return $member;
		}
		ksort($session);
		
		$sign = '';
		foreach ($session as $key => $value) {
			if ($key != 'sig') {
			$sign .= ($key.'='.$value);
			}
		}
		$sign .= APP_SHARED_SECRET;
		$sign = md5($sign);
		if ($session['sig'] == $sign && $session['expire'] > time()) {
			$member = array(
			'id' => intval($session['mid']),
			'secret' => $session['secret'],
			'sid' => $session['sid']
			);
		}
	}
	return $member;
}

$member = authOpenAPIMember();

if($member !== FALSE) {
  /* Пользователь авторизирован в Open API */
} else {
  /* Пользователь не авторизирован в Open API */
}
	/*
<!-- Put this script tag to the <head> of your page -->
<script type="text/javascript" src="http://userapi.com/js/api/openapi.js?49"></script>

<script type="text/javascript">
  VK.init({apiId: API_ID});
</script>

	<!-- Put this div tag to the place, where Auth block will be -->
<div id="vk_auth"></div>
<script type="text/javascript">
VK.Widgets.Auth("vk_auth", {width: "200px", onAuth: function(data) {
 alert('user '+data['uid']+' authorized');
} });
</script>
	
	*/
?>
</pre>
</div>
</body>
</html>




