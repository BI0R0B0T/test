<?php
  session_name("game");
  session_start();
?>
<!DOCTYPE HTML>
<html>
<head>
<title>Piraty</title>
<meta name="autor" content="Dolgov M.S." />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link href="../css/map.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../js/json2.js"></script>
<script type="text/javascript" src="../js/game.js"></script>
<!--
<script type="text/javascript" id="qbaka">
(function (w,d) {w.__qbaka_eh = w.onerror;w.__qbaka_reports=[];w.onerror = function () {
w.__qbaka_reports.push(arguments);};w.qbaka={exec:function(c){try{c()}catch(e){}},report:function(){},
_ldr:function(){w.qbaka._ldd=true;},customParams:{},set:function set(p, v){
qbaka.customParams[p]=v;},exec:function(f){try{f();}catch (e){qbaka.reportException(e);}},
reportException:function(){}};var e=d.createElement('script');e.id='qbaka';e.type='text/javascript';
e.async=!0;e.src='//cdn.qbaka.net/reporting.js';var s=d.getElementsByTagName('script')[0];
s.parentNode.insertBefore(e, s);w.addEventListener?w.addEventListener("load",w.qbaka._ldr,!1):
w.attachEvent("onload", w.qbaka._ldr);qbaka.key='11811407e9cfd4d3525af7db009d8629';})(window, document);
</script> -->
</head>
<body>
<?php
/*  if(need_to_add()){
	  $_SESSION = array(
		"player_id" => (int)ceil(rand(0,100)),
		"first_name" => "test".(int)rand(0,10),
		"last_name" => "test".(int)rand(0,10),
		"photo" => "no",
		"photo_rec" => "no",
		"SID" => session_id(),
		"play" => 0,
		"gameId" => null
	  );
	  gamelist::add_user();
  }else{
//  	var_dump($_SESSION);
  }
  */
  if(isset($_GET["g"])){
	//Запустить игру
     $id = $_GET["g"];
     if(!file_exists("../db/".$_GET["g"].".db")){
		 echo "<script type=\"text/javascript\">document.location.href = \"game.php\"</script>";
     }
print <<<LABEL
<script type="text/javascript" id="selector">
    window.onload = function(){
        globals.type = 2;
        game = new game();
        game.open($id);
    }
</script>
</div>
<div id="map">Map will be here</div>
</body>
</html>   
LABEL;
	  
  }else{
  //Запустить окно выбора игры
print <<<LABEL
<script type="text/javascript" id="selector">
    window.onload = function(){
        globals.type = 1;
        mapList.get();
        mapList.updateStart();
        var game = new game();
		drawPlayerInfo(getPlayerInfo(""));
     }
 </script>
<div id="select_game">
	<div id="map_list_big">Map list will be here</div>
	<div id="rule">
		<ul>
			<li><a href="javascript:exitFromGame()">exit </a></li>
			<li><a href="javascript:game.newGame()">start new game</a></li>
		</ul>
	</div>
	<div id="debug"></div>
</div>
<div id="player_info"></div>
<div id="create_game">
	<div id="select_option" class="pop_up">
		<header>Выберите тип игры</header>
		<ul>
			<li><a href="javascript:game.start(1)">1x1</a></li>
			<li><a href="javascript:game.start(2)">2x2</a></li>
			<li><a href="javascript:game.start(3)">4(Каждый сам за себя)</a></li>
		</ul>
		<footer><a href="javascript:game.cancel()">cancel </a></footer>
	</div>
</div>
<div id="wait_connection" class="pop_up">
	<header>Ожидаем остальных игроков</header>
	<span class="spinner"></span>
	<ul></ul>​
	<footer><a href="javascript:game.cancel()">cancel </a></footer>
</div>
</body>
</html>   
LABEL;
	  
  }
  function need_to_add(){
	  $name = array("player_id", "first_name", "last_name");
	  foreach($name as $v){
		  if(!isset($_SESSION[$v]) ||  "" == $_SESSION[$v]) { return TRUE; }
	  }
	  return FALSE;
  }
	function __autoload($class_name){
		include "../server/class_".$class_name.".php";
	}  
	exit();
//  var_dump($GLOBALS);
print <<<LABEL
<div id="left">
	<div id="rule">
		<ul>
			<li><a href="javascript:game.start()">Create Game </a></li>
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
