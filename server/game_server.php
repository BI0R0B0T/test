<?php
	session_start();
	if(isset($_GET)){
		switch($_GET["act"]){
			case "0": start_game(); break;
			case "1": stop_game(); break;
		}
	}
	function start_game(){
		require_once("class_cells.php");
		require_once("class_game.php");
		require_once("class_game_db.php");
		require_once("class_map.php");
		
	}
	function stop_game(){
		
	}
?>