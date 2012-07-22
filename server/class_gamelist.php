<?php
class gamelist{
	private static $gamelist = NULL;
	private static $game_db = NULL;
	/**
	* Проверяет можно ли подключится к данной игре в качестве игрока
	* @param int $game_id
	* @return boolean
	* @version 0.2
	*/
	public static function can_connect($game_id){
		self::get_db();
		$sql = "SELECT player1, player2, player3, player4, type FROM games WHERE game_db = ".$game_id;
		$res = self::$game_db->query($sql);
		game_stat::check_error($sql);
		$res = $res->fetchArray(SQLITE3_ASSOC);
		$max = (1==$res["type"]?2:4);
		$can = FALSE;
		for($i = 1; $i<=$max; $i++){
			if(!$res["player".$i]){
				$can = TRUE;
				break;
			}
		}
		return $can;
	}
	/**
	* Печатает список доступныъ игр
	* @return void
	*/
	public static function get_gamelist(){
		if(!self::$gamelist){
			new gamelist();
		}
		server::add("gamelist", self::$gamelist);
		server::output();
	}
	/**
	* Add game info in game_stat
	* 
	* @param string $game_id
	* @version 0.2
	*/
	public static function add_game($game_id){
		$player_number = (1==$_SESSION["game_type"]?2:4);
		self::get_db();
		$sql ="INSERT INTO games(id, game_db, player_number, player1, player2, player4, player3, played_now,";
		$sql.=" game_status, type, desc) VALUES(null, '".$game_id."', $player_number,".$_SESSION["player_id"].",null,";
		$sql.=" null, null, 1, 2, ".$_SESSION["game_type"].", '".($_SESSION["game_desc"]?$_SESSION["game_desc"]:$game_id)."')";
		$res = self::$game_db->query($sql);
		game_stat::check_error($sql);
		self::update_user(array("status"=>$_SESSION["play"],"game_id"=>self::$game_db->lastInsertRowID()));
	}
	public static function stopgame($gameid){
		self::get_db();
		$sql = "UPDATE games SET played_now = 0 WHERE game_db = ".$gameid;
		self::$game_db->query($sql);
		game_stat::check_error($sql);
	}
	public static function update_user($property){
		self::get_db();
		$sql = "UPDATE players SET";
		$str = "";
		foreach($property as $k=>$v){
			if($k == "player_id"){ continue;}
			if(is_null($v)){
				$str .= ", $k = null";
			}else{
				$str .= ", $k = ".(is_numeric($v)?$v:"\"".$v."\"");
			}
		}
		$str[0] = " ";
		$sql .=$str." WHERE players.id = ".$_SESSION["player_id"];
		$res = self::$game_db->query($sql);
		game_stat::check_error($sql);
	}
	public static function add_user(){
		self::get_db();
		$sql = "SELECT  count() AS count FROM players WHERE players.id = ".$_SESSION["player_id"];
		$res = self::$game_db->query($sql);
		game_stat::check_error($sql);
		$usr = $res->fetchArray(SQLITE3_ASSOC);
		if($usr["count"]){
			$sql = "UPDATE players SET status =  ".$_SESSION["play"].", game_id =  ";
			$sql.= ($_SESSION["gameId"]?"\"".$_SESSION["gameId"]."\"":"null");
			$sql.=", photo_rec =  \"".$_SESSION["photo_rec"]."\", photo =  \"".$_SESSION["photo"];
			$sql.="\", last_name =  \"".$_SESSION["last_name"]."\", first_name = \"";
			$sql.=$_SESSION["first_name"]."\"  WHERE players.id = ".$_SESSION["player_id"];
		}else{
			$sql = "INSERT INTO players(id, first_name, last_name, photo, photo_rec, status, game_id)";
			$sql.= "VALUES(".$_SESSION["player_id"].", \"".$_SESSION["first_name"]."\", \"";
			$sql.= $_SESSION["last_name"]."\", \"".$_SESSION["photo"]."\", \"".$_SESSION["photo_rec"]."\", ";
			$sql.= $_SESSION["play"].", \"".($_SESSION["gameId"]?$_SESSION["gameId"]:"null")."\")" ;
		}
		$res = self::$game_db->query($sql);
		game_stat::check_error($sql);
	}
	public static function finished_game(){
		
	}
	private function __construct(){
		self::get_db();
		$sql  = "SELECT  games.game_db, games.player_number, games.player1, games.player2,"; 
		$sql .= "games.player3, games.player4, games.game_status, games.type, games.desc, players.first_name,"; 
		$sql .= "players.last_name, players.id as player_id, players.photo, players.photo_rec "; 
		$sql .= "FROM players INNER JOIN games ON (players.game_id = games.id)"; 
		$sql .= "WHERE games.played_now = 1";
		$res = self::$game_db->query($sql);
		game_stat::check_error($sql);
		while($add = $res->fetchArray(SQLITE3_ASSOC)) {
			self::$gamelist[$add["game_db"]]["player_number"] = $add["player_number"];
			self::$gamelist[$add["game_db"]]["game_status"] = $add["game_status"];
			self::$gamelist[$add["game_db"]]["game_type"] = $add["type"];
			self::$gamelist[$add["game_db"]]["game_desc"] = $add["desc"];
			self::$gamelist[$add["game_db"]]["players"][] = new player($add);
		}
	}
	private function __clone(){
		
	}
	private static function get_db(){
		if(!self::$game_db){
			self::$game_db = game_stat::get_db();
		}
	}
	/**
	* Возвращает статус игры из БД game_stat
	* @return int $status
	* Возможные значения $status:
	* 								0 - error
	* 								1 - Идет игра
	* 								2 - Игра создана (в данный момент не хватает игроков)
	* 								3 - Игра закончена
	* 								4 - Игра прервана
	* 								5 - Идет игра (недостаток игроков)
	*/
	public static function get_game_status(){
		self::get_db();
		$sql = "SELECT games.game_status FROM games WHERE game_db = ".$_SESSION["gameId"];
		$status = self::$game_db->querySingle($sql);
		game_stat::check_error($sql);
		$_SESSION["game_status"] = (int)$status;
		return (int)$status;
	}
	/**
	* ОБновляет статус игры в БД game_stat
	* 
	* @param int $status
	* 								0 - error
	* 								1 - Идет игра
	* 								2 - Игра создана (в данный момент не хватает игроков)
	* 								3 - Игра закончена
	* 								4 - Игра прервана   
	* 								5 - Идет игра (недостаток игроков)
	*/
	private static function set_game_status($status){
		self::get_db();
		$sql = "UPDATE games SET games.game_status = ".(int)$status." WHERE game_db = ".$_SESSION["gameId"];
		self::$game_db->query($sql);
		game_stat::check_error($sql);
		$_SESSION["game_status"] = (int)$status;
	}
	/**
	* Обновляет статус игры
	*/
	public static function update_game_status(){
		self::get_db();
		$sql = "SELECT player_number, player1, player2, player3, player4, played_now, game_status FROM games WHERE game_db = ".$_SESSION["gameId"];
		$res = self::$game_db->query($sql);
		game_stat::check_error($sql);
		$res = $res->fetchArray(SQLITE3_ASSOC);
		if($res["played_now"] != 1){ 
			server::add("info","don't need update game_status");
			return;
		}
		$full = TRUE;
		for($i = 1; $i<=$res["player_number"]; $i++){
			if(!$res["player".$i]){
				$full = FALSE;
			}
		}
		if($full){
			if($res["game_status"] != 1) self::set_game_status(1);
		}else{
			if($res["game_status"] != 2) self::set_game_status(2);
		}
	}
}
?>
