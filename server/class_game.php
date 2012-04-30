<?php
class game{
	private static $game_id = NULL;
	private static $map = NULL;
	private static $player_id = NULL;
	private function __construct(){
		if(!isset($_SESSION["game_id"]) || !$_SESSION["game_id"]){
			self::$game_id = map::map_generate();
		}else{
			self::$game_id = $_SESSION["game_id"];
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
		echo json_encode(array("gameId"=>(int)self::$game_id, "SID" => session_id()));
		return self::$game_id;
	}
	public static function get_game($game_id){
		self::$game_id = $game_id;
		map::set_map_id($game_id);
		self::$map = map::get_map();
		return self::$map;
	}
	public static function stop_game($game_id){
		unlink($game_id);
		unset($_SESSION["game_id"]);
	}
	public static function convert_2_JSON($game_id){
		if(is_null(self::$map)){
			self::get_game($game_id);
		}
//		var_dump(self::$map);
		echo json_encode(
						array(	"map"=>self::$map, 
								"gameId"=>(int)self::$game_id, 
								"status"=>"OK",
								"SID" => session_id()
							)
						);
	}
	public static function open_cell($game_id, $cell_id){
		if(is_null(self::$map)){
			self::get_game($game_id);
		}
		$db = game_db::db_conn($game_id);
		$cell = self::$map[$cell_id]->open_cell($db,$cell_id);
		echo json_encode(array("cell"=>$cell, "status=>OK"));
	}
}
?>