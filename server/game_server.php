<?php

// Читаем данные, переданные в POST
// Заголовки ответа
header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');
header('Expires: ' . date('r'));
header('Last-Modified: ' . date('r'));
// Если данные были переданы...
if (isset($_POST["comandCode"]) && isset($_POST["comand"])){
	require_once("class_cells.php");
	require_once("class_game.php");
	require_once("class_game_db.php");
	require_once("class_map.php");
	switch ($_POST["comandCode"]){
		case 0: start_game(); break;
		case 1: stopgame($_POST["comand"]) ; break;
		case 2: give_game($_POST["comand"]); break;
		case 3: exit_from_game($_POST["comand"]); break;
		case 4: open_cell($_POST["comand"], $_POST["PHPSESSID"]); break;
		case 123: var_dump($GLOBALS);break;
		default: var_dump($_POST["comand"]);
	}
}else{
	// Данные не переданы
	echo json_encode(
		array
		(
			'result' => 'No data'
		)
	);
}
function start_game(){
	session_start();
	$_SESSION = array();
	if(isset($_SESSION["play"])){
		game::convert_2_JSON($_SESSION["gameId"]);
		setcookie("SID",session_id());
	}else{
		$_SESSION["play"] = 1;
		$_SESSION["gameId"] = game::start_game();
		setcookie("play",$_SESSION["play"]);
		setcookie("gameId",$_SESSION["gameId"]);
		setcookie("SID",session_id());
	}
	
}
function stopgame($SID){
	var_dump($SID);
//	echo json_encode(array("status"=>"Ok", "SID"=>$SID));
/*	
	echo "start delete";
	session_id($SID);
	session_start();
	if(isset($_SESSION["gameId"]) && !is_null($_SESSION["gameId"])){
		echo json_encode(array("status"=>"Ok"));
		game::stop_game($game_id);
		$_SESSION["play"] = 0;
		$_SESSION["gameId"] = null;
		setcookie("play",$_SESSION["play"]);
		setcookie("gameId","");
		setcookie("SID",$_COOKIE["PHPSESSID"]);	
	}else{
		echo json_encode(array("status"=>"FAIL"));
	}
*/	
}

function give_game($SID){
//	print_r($GLOBALS);
	session_id($SID);
	session_start();
	if(isset($_SESSION["gameId"]) && !is_null($_SESSION["gameId"])){
		game::convert_2_JSON($_SESSION["gameId"]);
	}else{
		echo json_encode(array("status"=>"FAIL"));
//		$game_id .=".db";
//		game::convert_2_JSON($game_id);
	}
}	
function open_cell($cell_id,$SID){
	session_id($SID);
	session_start();
	game::open_cell($_SESSION["gameId"],$cell_id);	
}
function exit_from_game($SID){
	session_id($SID);
	session_start();
	session_destroy();
	setcookie("play","");
	setcookie("gameId","");
	setcookie("PHPSESSID", "");
	echo json_encode(array("status"=>"Ok"));
}
?>