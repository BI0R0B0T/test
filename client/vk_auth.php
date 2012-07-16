<?php
/**
* Авторизация через VK.COM
*/
ini_set('display_errors','On');
require_once ("../server/config.php");
function __autoload($class_name){
	include_once "../server/class_".$class_name.".php";
} 
if(isset($_GET["access_token"]) && isset($_GET["expires_in"]) && isset($_GET["user_id"])){
	try{
		session_name("game");
		session_start();
  		$url = "https://api.vk.com/method/";
		$token = new token();
		$token->new_token($_GET["user_id"],$_GET["access_token"],$_GET["expires_in"]);
		$user_info = file_get_contents($url."users.get?uid={$token->player_id}&fields=first_name,last_name,photo,photo_rec&access_token=".$token->get_token());
		$user_info = json_decode($user_info);
		$user_info = (array)$user_info->response[0];
		$user_info['player_id'] = $user_info['uid'];
		$user = new player($user_info);
		$user->add_in_session();
		gamelist::add_user();
//		var_dump($token->get_from_db($user_info['uid']));
		header("Location: game.php");
	}catch(Exception $e){
		echo json_encode(array("status"=>"FAIL", "reason" =>$e->getMessage()));
	}
}

?>