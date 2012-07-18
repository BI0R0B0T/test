<?php
/**
* Класс синглтон с уникальным экземпляром игры.
* По сути дела в нем хранятся ссылки на все объекты в игре
*/
class game{
	private static $game_id = NULL;
	private static $map = NULL;
	private static $player_id = NULL;
	private static $units = array();
	private static $players = array();
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
			self::save_gamestat_in_db();
		}else{
			self::$game_id = $_SESSION["gameId"];
		}
	}
	public function __destruct(){}
	private function __clone(){}

    /**
     * Создаем новую игру
     * @static
     * @param int $type тип игры
     * @return null|string
     */
    public static function start_game($type){
		if((int)$type->type <=0 || (int)$type->type > 3){
			server::add("reason", "incorrect game type");
			server::return_fail();
		}else{
			$_SESSION["game_type"] = $type->type;
			$_SESSION["game_desc"] = $type->desc;
		}
		new game();
		$id = explode(".",self::$game_id);
		echo json_encode(array("gameId"=>$id[0], "SID" => session_id()));
		gamelist::add_game($id[0]);
		loger::save(0,json_encode(array("start game", $_SESSION["game_type"], $_SESSION["game_desc"])), $_SESSION["player_id"]);
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
		server::add("map",self::$map);
		server::add("gameId",$id[0]);
		server::add("SID",session_id());
		server::add("units",self::$units);
		server::add("you_move",1);
		server::output();
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
		server::add("cell",$cell);
		server::output();
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
	/**
	* @param object $unit
	*/
	public static function add_unit($unit){
		 self::$units[$unit->id] = $unit;
	}
	/**
	* @param int $id
	*/
	public static function get_unit($id){
		 if(!isset(self::$units[$id])){
			  self::$units[$id] = unit::get_unit_from_db($id);
		 }
		 return self::$units[$id];
	}	
	public static function get_units(){
		 return self::$units;
	}	
	/**
	* @param object $player
	*/
	public static function add_players($player){
		if(!isset(self::$players[$player->user_id])){
			self::$players[$player->user_id] = $player;
		}
	}
	/**
	* @param int $id
	*/
	public static function get_player($id){
		if(!isset(self::$players[$id])){
			self::add_players(user_info::get_from_db($id));
		}
		return self::$players[$id];
	}
	public static function get_player_by_number($number){
		foreach(self::$players as $id=>$player){
			if($number == $player->player_number){
				return $player;
			}
		}
		$player = user_info::get_from_db_by_number($number);
		$id = $player->user_id;
		self::add_players($player);
		return self::get_player($id);
	}
	/**
	* Сохраняет новую игру в БД
	*/
	private static function save_gamestat_in_db(){
		$db = game_db::db_conn();
		$sql = "INSERT INTO games (id, player1_id, player2_id, player3_id, player4_id, game_type) VALUES (null,";
		$sql.=$_SESSION["player_id"].",null,null,null,".$_SESSION["game_type"].")";
		$db->query($sql);
		game_db::check_error($sql);
	}
}
?>
