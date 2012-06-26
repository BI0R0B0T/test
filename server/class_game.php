<?php
/**
* Класс синглтон с уникальным экземпляром игры.
*/
class game{
	private static $game_id = NULL;
	private static $map = NULL;
	private static $player_id = NULL;
	private static $units = array();
	private function __construct(){
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
	}
	public function __destruct(){
		
	}
	private function __clone(){
		
	}
	public static function start_game(){
		new game();
		$id = explode(".",self::$game_id);
		echo json_encode(array("gameId"=>$id[0], "SID" => session_id()));
		gamelist::add_game($id[0]);
		loger::save(0,json_encode(array("start game")), $_SESSION["player_id"]);
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
		gamelist::stopgame($game_id);
	}
	public static function convert_2_JSON($game_id){
		if(!isset($_SESSION["gameId"]) || is_null($_SESSION["gameId"])){ $_SESSION["gameId"] = $game_id;}
		if(is_null(self::$map)){
			self::get_game($game_id);
		}
		if(!self::$units){
			self::$units = unit::get_units_from_db();
		}
		$id = explode(".",self::$game_id);
		echo json_encode(
						array(	"map"=>self::$map, 
								"gameId"=>$id[0], 
								"status"=>"OK",
								"SID" => session_id(),
								"units" =>self::$units
							)
						);
	}
	/**
	* Функция открывает закрытую клетку
	* @param int $game_id идентификатор текущей игры (в последующей версии нужно убрать)
	* @param int $cell_id идентификатор клетки
	* @return void
	* @version 0.1
	*/
	public static function open_cell($game_id, $cell_id){
		if(is_null(self::$map)){
			self::get_game($game_id);
		}
		$db = game_db::db_conn($game_id);
		$cell = self::$map[$cell_id]->open_cell($db,$cell_id);
		echo json_encode(array("cell"=>$cell, "status"=>"OK"));
	}
	/**
	* Фнкция добавляет нового игрока в игру
	* @version 0.1
	*/
	public static function add_player(){
		self::$player_id= new user_info($_SESSION["player_id"], $_SESSION["first_name"],
										$_SESSION["last_name"],$_SESSION["photo"],
										$_SESSION["photo_rec"],1,$_SESSION["play"]
										);
		$_SESSION["player_number"] = self::$player_id->save_in_db();
		loger::save(1,json_encode(array("add_player")), $_SESSION["player_id"]);
		for($i = 0; $i < 3; $i++){
			unit::born_unit_on_ship($_SESSION["player_number"]-1);
		}
		self::convert_2_JSON($_SESSION["gameId"]);
	}
}
?>