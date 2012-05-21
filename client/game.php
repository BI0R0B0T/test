<?php
  session_name("game");
  session_start();
  include_once("../server/class_game_list.php");
  $_SESSION = array(
  	"player_id" => ceil(rand(0,100)),
  	"first_name" => "test".(int)rand(0,10),
  	"last_name" => "test".(int)rand(0,10),
  	"photo" => "no",
  	"photo_rec" => "no",
  	"SID" => session_id(),
  	"play" => 0,
  	"gameId" => null
  );
  gamelist::add_user();
//  var_dump($GLOBALS);
print <<<LABEL
<!DOCTYPE HTML>
<html>
<head>
<title>Create Game</title>
<meta name="" content="">
<link href="../css/map.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../js/json2.js"></script>
<script type="text/javascript" src="../js/game.js"></script>
</head>
<body>
<ul>
	<li><a href="javascript:game.start()">Start Game </a></li>
	<li><a href="javascript:game.stop()">Stop Game </a></li>
	<li><a href="javascript:exitFromGame()">exit </a></li>
</ul>
<div id="map_list">Map list will be here</div>
<div id="map">Map will be here</div>
</body>
</html>   
LABEL;

?>
