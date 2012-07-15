<?php
/**
* Авторизация через VK.COM
*/
ini_set('display_errors','On');
require_once ("server/config.php");
function __autoload($class_name){
	include_once "server/class_".$class_name.".php";
} 
if(isset($_GET["access_token"]) && isset($_GET["expires_in"]) && isset($_GET["user_id"])){
	$url = "https://api.vk.com/method/";
	$token = new token();
	$token->new_token($_GET["user_id"],$_GET["access_token"],$_GET["expires_in"]);
	$user_info = file_get_contents($url."users.get?uid={$token->player_id}&fields=first_name,last_name,photo,photo_rec&access_token=".$token->get_token());
	$user_info = json_decode($user_info);
	$user_info = (array)$user_info->response[0];
	$user_info['player_id'] = $user_info['uid'];
//	var_dump($user_info);
	$user = new player($user_info);
	$user->add_in_session();
	gamelist::add_user();
//	var_dump($user);
}
exit();
die();
	var_dump($_GET);
//	var_dump($_SESSION);
//	var_dump($_COOKIE['vk_app_'.APP_ID]);
	
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
 //   $sign .= APP_SHARED_SECRET;
//	printf("a= %s<br>".PHP_EOL."s= %s<br>".PHP_EOL,$app_cookie,$sign);
    $sign = md5($sign);
    if ($session['sig'] == $sign && $session['expire'] > time()) {
      $member = array(
        'id' => intval($session['mid']),
        'secret' => $session['secret'],
        'sid' => $session['sid']
      );
    }else{
//		printf("session['sig'] = %s<br>".PHP_EOL."sign = %s",$session['sig'],$sign);
	}
  }
  return $member;
}

$member = authOpenAPIMember();
if($member !== FALSE) {
  /* Пользователь авторизирован в Open API */
  $add = array();
  $user = player::new_from_get();
  var_dump($user);
} else {
  /* Пользователь не авторизирован в Open API */
//  var_dump($member);
 
}
?>