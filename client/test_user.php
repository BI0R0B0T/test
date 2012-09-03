<?php
session_name("game");
session_start();
ini_set('display_errors','On');
require_once ("../server/config.php");
function __autoload($class_name){
	include_once "../server/class_".$class_name.".php";
} 
$user_info = array(
					"player_id" => 1,
					"first_name" => "test",
					"last_name" => "user",
					"photo" => null,
					"photo_rec" => null
);
$user = new player($user_info);
$user->add_in_session();
$user->add_in_db();

//var_dump($_SESSION);
header("Location: game.php");
?>