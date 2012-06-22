<?php
session_name("game");
session_start();
// Читаем данные, переданные в POST
$rawPost = file_get_contents('php://input');
// Заголовки ответа
date_default_timezone_set("Europe/Moscow");
header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');
header('Expires: ' . date('r'));
header('Last-Modified: ' . date('r'));
// Если данные были переданы...
$req = json_decode($rawPost);
if($req){
	require_once("class_cells.php");
	require_once("class_game.php");
	require_once("class_game_db.php");
	require_once("class_map.php");
	switch ($req->comandCode){
		case 0: start_game(); break;
		case 1: stopgame() ; break;
		case 2: give_game(); break;
		case 3: exit_from_game(); break;
		case 4: open_cell($req->comand); break;
		case 5: display_game_list();break;
		case 6: drop_game($req->comand);break;
		case 7: open_game($req->comand);break;
		case 8: connect_game($req->comand);break;
		case 9: move_unit($req->comand);break;
		default: echo json_encode( array ( 'status' => 'FAIL', "reason" => "incorrect comand" ) );
	}
}else{
	// Данные не переданы
	echo json_encode( array ( 'result' => 'No data' ) );
}
/**
* Запускаем игру (происходит создание новой карты)
*/
function start_game(){
	/**
	* Если игра уже создана
	*/
	if(isset($_SESSION["gameId"]) && !is_null($_SESSION["gameId"])){
		game::convert_2_JSON($_SESSION["gameId"]);
	}else{
		$_SESSION["play"] = 1;
		$_SESSION["gameId"] = game::start_game();
		setcookie("SID",session_id());
	}
	
}
/**
* Останавливаем игру (при этом игра удаляется))
*/
function stopgame(){
	if(isset($_SESSION["gameId"]) && !is_null($_SESSION["gameId"])){
		echo json_encode(array("status"=>"Ok"));
		game::stop_game($_SESSION["gameId"]);
		$_SESSION["play"] = 0;
		$_SESSION["gameId"] = null;
	}else{
		echo json_encode(array("status"=>"FAIL"));
	}	
}
/**
* Возвращает текущую игру
*/
function give_game(){
	if(isset($_SESSION["gameId"]) && !is_null($_SESSION["gameId"])){
		game::convert_2_JSON($_SESSION["gameId"]);
	}else{
		echo json_encode(array("status"=>"FAIL", $_SESSION));
	}
}	
/**
* Открываем клетку (открыта для тестирования, потом закрою)
* @param int $cell_id
* 
*/
function open_cell($cell_id){
	game::open_cell($_SESSION["gameId"],$cell_id);	
}
/**
* выход из игры 
*/
function exit_from_game(){
	session_destroy();
	$_SESSION = array();
	$_COOKIE = array();
	echo json_encode(array("status"=>"Ok"));
}
/**
* выводит список доступных игр
*/
function display_game_list(){
	include_once("class_game_list.php");
	gamelist::get_gamelist();
}
/**
* Удаляет игру (будет доступна только для админа)
* @param int $game_id
* 
*/
function drop_game($game_id){
	game::stop_game($game_id);
	echo json_encode(array("status"=>"Ok"));
	$_SESSION["play"] = 0;
	$_SESSION["gameId"] = null;
}
/**
* просматриваем игру
* @param int $game_id
* 
*/
function open_game($game_id){
	$_SESSION["play"] = 0;
	$_SESSION["gameId"] = $game_id;
	give_game();
}
/**
* Присоединяемся к игре
* @param int $game_id
* 
*/
function connect_game($game_id){
	if(isset($_SESSION["gameId"]) && $_SESSION["gameId"]){
		give_game();
	}else{
		$_SESSION["play"] = 1;
		$_SESSION["gameId"] = $game_id;
		game::add_player();		
	}
}
/**
* Перемещение юнитов по карте
* @param array $move
* @version 0.1
*/
function move_unit($move){
	include_once("class_unit.php");
	$unit = unit::get_unit_from_db($move[0]);
	if($unit->checkPossibleMove()){
		$unit->move_to($move[1]);
	}else{
		echo json_encode(array("status"=>"FAIL"));
	}
}
?>