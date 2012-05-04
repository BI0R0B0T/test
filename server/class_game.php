<?php
class game{
	private static $game_id = NULL;
	private static $map = NULL;
	private static $player_id = NULL;
	private static $units = array();
	private function __construct(){
		include_once("class_unit.php");
		include_once("class_userinfo.php");
		if(!isset($_SESSION["gameId"]) || !$_SESSION["gameId"]){
			self::$game_id = map::map_generate();
			$_SESSION["gameId"] = self::$game_id;
			self::$player_id= new user_info($_SESSION["player_id"], $_SESSION["first_name"],
											$_SESSION["last_name"],$_SESSION["photo"],
											$_SESSION["photo_rec"],1,$_SESSION["play"]
											);
			$_SESSION["player_number"] = self::$player_id->save_in_db();
			for($i = 0; $i < 3; $i++){
				unit::born_unit_on_ship($_SESSION["player_number"]-1);
			}
		}else{
			self::$game_id = $_SESSION["gameId"];
		}
		
//		self::$map = map::get_map(self::$game_id);
//		self::$game_id = self::$map->get_map_id();
	}
	public function __destruct(){
		
	}
	private function __clone(){
		
	}
	public static function start_game(){
		new game();
		$id = explode(".",self::$game_id);
		echo json_encode(array("gameId"=>$id[0], "SID" => session_id()));
		include_once("class_game_list.php");
		gamelist::add_game($id[0]);
		return self::$game_id;
	}
	public static function get_game($game_id){
		self::$game_id = $game_id;
		map::set_map_id($game_id);
		self::$map = map::get_map();
		return self::$map;
	}
	public static function stop_game($game_id){
		if(file_exists(game_db::ADR.$game_id)) {unlink(game_db::ADR.$game_id);}
		include_once("class_game_list.php");
		gamelist::stopgame($game_id);
	}
	public static function convert_2_JSON($game_id){
		if(!isset($_SESSION["gameId"]) || is_null($_SESSION["gameId"])){ $_SESSION["gameId"] = $game_id;}
		if(is_null(self::$map)){
			self::get_game($game_id);
		}
		if(!self::$units){
			include_once("class_unit.php");
			self::$units = unit::get_units_from_db();
		}
		$id = explode(".",self::$game_id);
//		var_dump(self::$map);
		echo json_encode(
						array(	"map"=>self::$map, 
								"gameId"=>$id[0], 
								"status"=>"OK",
								"SID" => session_id(),
								"units" =>self::$units
							)
						);
	}
	public static function open_cell($game_id, $cell_id){
		if(is_null(self::$map)){
			self::get_game($game_id);
		}
		$db = game_db::db_conn($game_id);
		$cell = self::$map[$cell_id]->open_cell($db,$cell_id);
		echo json_encode(array("cell"=>$cell, "status"=>"OK"));
	}
	public static function add_player(){
		include_once("class_unit.php");
		include_once("class_userinfo.php");
		self::$player_id= new user_info($_SESSION["player_id"], $_SESSION["first_name"],
										$_SESSION["last_name"],$_SESSION["photo"],
										$_SESSION["photo_rec"],1,$_SESSION["play"]
										);
		$_SESSION["player_number"] = self::$player_id->save_in_db();
		for($i = 0; $i < 3; $i++){
			unit::born_unit_on_ship($_SESSION["player_number"]-1);
		}
		self::convert_2_JSON($_SESSION["gameId"]);
	}
}
?>