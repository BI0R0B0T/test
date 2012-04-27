<?php
	session_start();
	if(isset($_GET)){
		switch($_GET["act"]){
			case "0": start_game(); break;
			case "1": stop_game(); break;
		}
	}
	function start_game(){
		
	}
	function stop_game(){
		
	}
?>