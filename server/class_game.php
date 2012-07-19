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
		loger::save(0,json_encode(array("start game", $_SESSION["game_type"], $_SESSION["game_desc"])));
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
	* @param int $cell_id идентификатор клетки
	* @return void
	* @version 0.1
	*/
	public static function open_cell($cell_id){
		self::$map[$cell_id]=cells::open_cell($cell_id);
		server::add("cell",self::$map[$cell_id]);
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
		loger::save(1,json_encode(array("add_player")));
		for($i = 0; $i < 3; $i++){
			unit::born_unit_on_ship($_SESSION["player_number"]-1);
		}
		gamelist::update_game_status();
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
			  self::$units = unit::get_units_from_db();
		 }
		 return self::$units[$id];
	}	
	public static function get_units(){
		 return self::$units;
	}	
	/**
	* @param int $cell_id
	* @return array
	*/
	public static function get_units_from_cell($cell_id){
		$return = array();
		foreach(self::$units as $id=>$unit){
			if($unit->position == $cell_id){
				$return[$id] = $unit;
			}
		}
		return $return;
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
	/**
	* Возвращает ID игрока чей ход следующий
	* @return int 
	* @version 0.1
	*/
	public static function who_next(){
		$previous_id = loger::who_was_last();
		$previous = self::get_player($previous_id);
		$db = game_db::db_conn();
		$sql = "SELECT games.player1_id,games.player2_id,games.player3_id,games.player4_id,games.game_type ";
		$sql.="FROM games";
		$res = $db->query($sql);
		game_db::check_error();
		$res = $res->fetchArray(SQLITE3_ASSOC);
		$max = (1==$res["game_type"]?2:4);
		if($previous->player_number == $max){
			$next_number = 1;
		}else{
			$next_number = $previous->player_number+1;
		}	
		return $res["player".$next_number."_id"];
	}
		/**
	* Проверка на то может ли данный игрок сейчас ходить
	* @return boolean
	* @version 0.3
	*/
	public static function checkPossibleMove(){
		if(self::who_next() == $_SESSION["player_id"]){
			return TRUE;
		}else{
			server::add("reason", "This isn't your turn. Reason 2 (id = ".$_SESSION["player_id"]
											." want ".$this->who_will_next($previous).")");
			server::return_fail();
		}
	}
	/**
	* @param int $cell_id id клетки
	* @return object class cells
	* @version 0.1
	*/
	public static function get_cell($cell_id){
		if(!isset(self::$map[$cell_id])){
			self::$map[$cell_id] = cells::get_cell_from_db($cell_id);
		}
		return self::$map[$cell_id];
	}
	/**
	* @param object $cell class cells
	* @version 0.1
	*/
	public static function add_cell($cell){
		self::$map[$cell->cell_id] = $cell;
	}
}
?>
