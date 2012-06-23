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
<script type="text/javascript" id="qbaka">
(function (w,d) {w.__qbaka_eh = w.onerror;w.__qbaka_reports=[];w.onerror = function () {
w.__qbaka_reports.push(arguments);};w.qbaka={exec:function(c){try{c()}catch(e){}},report:function(){},
_ldr:function(){w.qbaka._ldd=true;},customParams:{},set:function set(p, v){
qbaka.customParams[p]=v;},exec:function(f){try{f();}catch (e){qbaka.reportException(e);}},
reportException:function(){}};var e=d.createElement('script');e.id='qbaka';e.type='text/javascript';
e.async=!0;e.src='//cdn.qbaka.net/reporting.js';var s=d.getElementsByTagName('script')[0];
s.parentNode.insertBefore(e, s);w.addEventListener?w.addEventListener("load",w.qbaka._ldr,!1):
w.attachEvent("onload", w.qbaka._ldr);qbaka.key='11811407e9cfd4d3525af7db009d8629';})(window, document);
</script>
</head>
<body>
<div id="left">
	<div id="rule">
		<ul>
			<li><a href="javascript:game.start()">Start Game </a></li>
			<li><a href="javascript:game.stop()">Stop Game </a></li>
			<li><a href="javascript:exitFromGame()">exit </a></li>
		</ul>
	</div>
	<div id="map_list">Map list will be here</div>
	<div id="debug"></div>
</div>
<div id="map">Map will be here</div>
</body>
</html>   
LABEL;

?>
