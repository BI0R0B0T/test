<?php
class gamelist{
	private static $gamelist = NULL;
	private static $game_db = NULL;
	const DBNAME = "../db/game_stat.db";
	public static function get_gamelist(){
		if(!self::$gamelist){
			new gamelist();
		}
		echo json_encode(array("gamelist"=>self::$gamelist, "status"=>"OK"));
	}
	public static function add_game(){
		
	}
	public static function finished_game(){
		
	}
	private function __construct(){
		self::get_db();
		$sql  = "SELECT  games.game_db, games.player_number, games.player1, games.player2,"; 
		$sql .= "games.player3, games.player4, games.game_status, players.first_name,"; 
		$sql .= "players.last_name, players.id as player_id, players.photo, players.photo_rec "; 
		$sql .= "FROM players INNER JOIN games ON (players.game_id = games.id)"; 
		$sql .= "WHERE games.played_now = 1";
		$res = self::$game_db->query($sql);
		while($add = $res->fetchArray(SQLITE3_ASSOC)) {
			self::$gamelist[$add["game_db"]]["player_number"] = $add["player_number"];
			self::$gamelist[$add["game_db"]]["game_status"] = $add["game_status"];
			self::$gamelist[$add["game_db"]]["players"][] = new player($add);
		}
//		var_dump(self::$gamelist);
	}
	private function __clone(){
		
	}
	private static function get_db(){
		if(!self::$game_db){
			self::$game_db = new SQLite3(self::DBNAME);
		}
		self::$game_db;
	}
}
class player{
	public $player_id;
	public $first_name;
	public $last_name;
	public $photo;
	public $photo_rec;
	function __construct($add){
		 $this->player_id = $add["player_id"];
		 $this->first_name 	= is_null($add["first_name"])?"no":$add["first_name"];
		 $this->last_name 	= is_null($add["last_name"])?"no":$add["last_name"];
		 $this->photo 		= is_null($add["photo"])?"no":$add["photo"];
		 $this->photo_rec 	= is_null($add["photo_rec"])?"no":$add["photo_rec"];
	}
}
?>