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
//$_POST["comandCode"] = 1;
// Если данные были переданы...
//if(isset($_POST["code"]) && isset($_POST["cmd"])){
$req = json_decode($rawPost);
//var_dump($req);
if($req){
//	var_dump($req);
//	var_dump($_POST);
//	var_dump($GLOBALS);
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
		default: var_dump($req->comand);
	}
}else{
	// Данные не переданы
//	var_dump($_POST);
	var_dump($GLOBALS);
	echo json_encode(
		array
		(
			'result' => 'No data'
		)
	);
}
function start_game(){
//	$_SESSION = array();
	if(isset($_SESSION["gameId"]) && !is_null($_SESSION["gameId"])){
		game::convert_2_JSON($_SESSION["gameId"]);
//		setcookie("SID",session_id());
	}else{
		$_SESSION["play"] = 1;
		$_SESSION["gameId"] = game::start_game();
		setcookie("play",$_SESSION["play"]);
		setcookie("gameId",$_SESSION["gameId"]);
		setcookie("SID",session_id());
	}
	
}
function stopgame(){
//	var_dump($SID);
//	echo json_encode(array("status"=>"Ok", "SID"=>$SID));
//	echo "start delete";
//	session_id($SID);
//	session_start();
	if(isset($_SESSION["gameId"]) && !is_null($_SESSION["gameId"])){
		echo json_encode(array("status"=>"Ok"));
		game::stop_game($_SESSION["gameId"]);
		$_SESSION["play"] = 0;
		$_SESSION["gameId"] = null;
		setcookie("play",$_SESSION["play"]);
		setcookie("gameId","");
	}else{
		echo json_encode(array("status"=>"FAIL"));
	}	
}

function give_game(){
	if(isset($_SESSION["gameId"]) && !is_null($_SESSION["gameId"])){
		game::convert_2_JSON($_SESSION["gameId"]);
	}else{
		echo json_encode(array("status"=>"FAIL"));
	}
}	
function open_cell($cell_id){
	game::open_cell($_SESSION["gameId"],$cell_id);	
}
function exit_from_game(){
	session_destroy();
	$_SESSION = array();
	$_COOKIE = array();
	echo json_encode(array("status"=>"Ok"));
}
function display_game_list(){
	include_once("class_game_list.php");
	gamelist::get_gamelist();
}
function drop_game($game_id){
	game::stop_game($game_id);
	echo json_encode(array("status"=>"Ok"));
}
function open_game($game_id){
	$_SESSION["play"] = 1;
	$_SESSION["gameId"] = $game_id.".db";
	give_game();
//	var_dump($_SESSION["gameId"]) ;
}
?>