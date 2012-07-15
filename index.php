<!DOCTYPE HTML>
<html>
<head>
	<title>Игра "ПИРАТЫ" он-лайн аналог игры шакал</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="autor" content="Dolgov M.S." />
</head>
<body>
<?php
	ini_set('display_errors','On');
	require_once("server/config.php");
	echo "<a href=\"http://oauth.vk.com/authorize?client_id=".APP_ID."&scope=friends,offline&redirect_uri=".
	"http://kodomo.fbb.msu.ru/~dolgov/piraty/graber.html&display=popup&response_type=token\"".
	"title=\"Зайти через ВКонтакте\">Зайти через ВКонтакте</a>";
?>
</body>
</html>




