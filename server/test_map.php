<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>draw map</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="test_map.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
	include_once("class_map.php");
	$new_map = map::get_map();
	$need_rotate = array(	"move_up", 
							"strelka_dv_po_diag", 
							"strelka_po_diag", 
							"strelka_ne_w_s",
							"strelka_l_r",
							"gun",
							"ship");
//	var_dump($new_map);
//	exit();
	$r = 0;
	$c = 0;
	echo "<div id =\"map\">\n"; 
	foreach($new_map as $cell){
		$id = $cell->cell_id;
		$class = get_class($cell);
		if(in_array($class, $need_rotate)){
			$class = $class."_".$cell->rotate;
		}
		echo "<div id =\"$id\" class =\"$class\">$id</div>\n";
	}
	echo "</div>"; 
//	print_r($new_map); 
?>
</body>
</html>