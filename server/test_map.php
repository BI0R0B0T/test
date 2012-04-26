<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>draw map</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="test_map.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
//	$style_name = array("right", "up", "left", "down");
	$style_name = array("up", "left", "down", "right");
//	$style_name = array("up-r", "up-l", "d-l", "d-r");
	include_once("class_map.php");
	$new_map = map::get_map();
	var_dump($new_map);
	exit();
	$r = 0;
	$c = 0;
	echo "<table>"; 
	foreach($new_map as $cell){
	if(0 == $c){
		echo "<tr>\n";
	}
	echo "\t<td class = \"".$style_name[$cell->rotate]."\">".@$cell->possible_next_cells[0]."</td>\n";
	$c++;
	if(13 == $c){
		$c = 0;
		$r++;
		echo "</tr>\n";
	}
	}
	echo "</table>"; 
//	print_r($new_map); 
?>
</body>
</html>