<?php
session_name("game");
session_start();
require_once("config.php");
// Читаем данные, переданные в POST
$rawPost = file_get_contents('php://input');
// Заголовки ответа
date_default_timezone_set("Europe/Moscow");
header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');
header('Expires: ' . date('r'));
header('Last-Modified: ' . date('r'));
/**
 * Автоматическое подключение классовvk_auth.php
 * @param string $class_name имя класса
 * @version 0.1
 */
function __autoload($class_name){
	include "class_".$class_name.".php";
}
// Передаем данные серверу на обработку
try{
	server::input($rawPost);
}catch(Exception $e){
	echo json_encode(array("status"=>"FAIL", "reason" =>$e->getMessage()));
}
?>