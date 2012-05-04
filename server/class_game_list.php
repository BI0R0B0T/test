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
	public static function add_game($game_id, $player_number = 4){
		self::get_db();
		$sql ="INSERT INTO games( id, game_db, player_number, player1, player2, player4, player3, played_now, game_status) VALUES(";
		$sql.="null, \"$game_id\", $player_number,".$_SESSION["player_id"].",null,null,null, 1, ".$_SESSION["play"].")";
		$res = self::$game_db->query($sql);
//		var_dump(self::$game_db->lastInsertRowID());
/*		if(!$res){"FALSE";
		}else{
			var_dump($res->fetchArray(SQLITE3_ASSOC));
			var_dump(self::$game_db->lastErrorMsg());
			var_dump($game_id);
			echo $sql."<br>";
		}
*/		self::update_user(array("status"=>$_SESSION["play"],"game_id"=>self::$game_db->lastInsertRowID()));
	}
	public static function stopgame($gameid){
		self::get_db();
		$sql = "UPDATE games SET played_now = 0 WHERE game_db = ".$gameid;
		self::$game_db->query($sql);
/*		$res = 
		if(!$res){"FALSE";
		}else{
			var_dump($res);
			print_r($res->fetchArray(SQLITE3_ASSOC));
			echo "<br>";			
			print_r(self::$game_db->lastErrorMsg());
			echo "<br>";			
			print_r($gameid);
			echo "<br>";			
			echo $sql."<br>";
		}*/
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
/*		if(!$res){"FALSE";
		}else{
			var_dump($res->fetchArray(SQLITE3_ASSOC));
			var_dump(self::$game_db->lastErrorMsg());
			var_dump($_SESSION["player_id"]);
			echo $sql."<br>";
		}  */
	}
	public static function add_user(){
		self::get_db();
		$sqla = "SELECT  count() AS count FROM players WHERE players.id = ".$_SESSION["player_id"];
		$res = self::$game_db->query($sqla);
		$usr = $res->fetchArray(SQLITE3_ASSOC);
		if($usr["count"]){
			$sql = "UPDATE players SET status =  ".$_SESSION["play"].", game_id =  ".($_SESSION["gameId"]?"\"".$_SESSION["gameId"]."\"":"null");
			$sql .=", photo_rec =  \"".$_SESSION["photo_rec"]."\", photo =  \"".$_SESSION["photo"]."\", last_name =  \"".$_SESSION["last_name"];
			$sql .="\", first_name = \"".$_SESSION["first_name"]."\"  WHERE players.id = ".$_SESSION["player_id"];
		}else{
			$sql = "INSERT INTO players(id, first_name, last_name, photo, photo_rec, status, game_id) VALUES(";
			$sql .= $_SESSION["player_id"].", \"".$_SESSION["first_name"]."\", \"".$_SESSION["last_name"]."\", \"".$_SESSION["photo"]."\", \"";
			$sql .= $_SESSION["photo_rec"]."\", ".$_SESSION["play"].", \"".($_SESSION["gameId"]?$_SESSION["gameId"]:"null")."\")" ;
		}
	//	echo $sql."<br>";
		$res = self::$game_db->query($sql);
/*		if(!$res){
			"FALSE";
		}else{
			var_dump($res);
			var_dump(self::$game_db->lastErrorMsg());
			var_dump($res->fetchArray(SQLITE3_ASSOC));
		} */
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
//		var_dump($res->fetchArray(SQLITE3_ASSOC));
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