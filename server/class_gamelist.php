<?php
class gamelist{
	public static $players = array();
	private static $gamelist = NULL;
	/**
	* Проверяет можно ли подключится к данной игре в качестве игрока
	* @param int $game_id
	* @return boolean
	* @version 0.3
	*/
	public static function can_connect($game_id){
		try{
			$db = game_stat::get_db();
			$sth = $db->prepare("SELECT player1, player2, player3, player4, type FROM games ".
								"WHERE game_db = ?");
			$sth->bindParam(1,$game_id);
			$sth->setFetchMode(PDO::FETCH_ASSOC);
			$sth->execute();
			$res = $sth->fetch();
		}catch(PDOException $e){
			server::return_fail($e);
		}
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
	* @return void
	* @version 0.3
	*/
	public static function add_game($game_id){
		try{
		$db = game_stat::get_db();
		$sth = $db->prepare("INSERT INTO games(id, game_db, player_number, player1, player2, ".
					"player4, player3, played_now, game_status, type, desc) VALUES(null,".
					":id,:player_number,:player_id,null,null, null, 1, 2, :type,:desc)");
		$sth->bindParam(":id",$game_id);
		$i = (1==$_SESSION["game_type"]?2:4);
		$sth->bindParam(":player_number",$i);
		$sth->bindParam(":player_id",$_SESSION["player_id"]);
		$sth->bindParam(":type",$_SESSION["game_type"]);
		$i = ($_SESSION["game_desc"]?$_SESSION["game_desc"]:$game_id);
		$sth->bindParam(":desc", $i);
		$sth->execute();
		self::get_player($_SESSION["player_id"])
			->update_property(array("status"=>$_SESSION["status"],
									"game_id"=>$db->lastInsertID()));
		}catch(PDOException $e){
			server::return_fail($e);
		}
	}
	
	public static function stopgame($gameid){
		try{
			$db = game_stat::get_db();
			$sth = $db->prepare("UPDATE games SET played_now = 0 WHERE game_db = ?");
			$sth->execute(array($gameid));
		}catch(PDOException $e){
			server::return_fail($e);
		}
	}
	/**
	* Добавляет ссылку на объект игрока в gamelist
	* @param object $player class player
	* @return void
	*/ 
	public static function add_player($player){
		self::$players[$player->player_id] = $player;
	}
	/**
	* Возвращает объект игрока 
	* @param int $player_id
	* return object class player
	*/ 
	public static function get_player($player_id){
		if(!isset(self::$players[$player_id])){
			self::$players[$player_id] = player::get_from_db($player_id);
		}
		return self::$players[$player_id];
	}

	public static function finished_game(){
		
	}
	private function __construct(){
		try{
			$db = game_stat::get_db();
			$sql  = "SELECT  games.game_db, games.player_number, games.player1, games.player2,"; 
			$sql .= "games.player3, games.player4, games.game_status, games.type, games.desc, ";
			$sql .= "players.first_name,"; 
			$sql .= "players.last_name, players.player_id, players.photo, players.photo_rec "; 
			$sql .= "FROM players INNER JOIN games ON (players.game_id = games.id)"; 
			$sql .= "WHERE games.played_now = 1";
			$sth = $db->query($sql);
			$sth->setFetchMode(PDO::FETCH_ASSOC);
			while($add = $sth->fetch()) {
				self::$gamelist[$add["game_db"]]["player_number"] = $add["player_number"];
				self::$gamelist[$add["game_db"]]["game_status"] = $add["game_status"];
				self::$gamelist[$add["game_db"]]["game_type"] = $add["type"];
				self::$gamelist[$add["game_db"]]["game_desc"] = $add["desc"];
				$player = new player($add);
				self::$gamelist[$add["game_db"]]["players"][] = $player;
				self::add_player($player);
			}			
		}catch(PDOException $e){
			server::return_fail($e);
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
		try{
			$db = game_stat::get_db();
			$sth = $db->prepare("SELECT games.game_status FROM games WHERE game_db = ?");
			$sth->execute(array($_SESSION["gameId"]));			
		}catch(PDOException $e){
			server::return_fail($e);
		}
		$status = $sth->fetch(PDO::FETCH_ASSOC);
		$status = (int)$status["game_status"];
		$_SESSION["game_status"] = $status;
		return $status;
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
		try{
			$db = game_stat::get_db();
			$sth = $db->prepare("UPDATE games SET games.game_status = ? WHERE game_db = ?");
			$sth->execute(array((int)$status, $_SESSION["gameId"]));
		}catch(PDOException $e){
			server::return_fail($e);
		}
	}
	/**
	* Обновляет статус игры
	*/
	public static function update_game_status(){
		try{
			$db = game_stat::get_db();
			$sth = $db->prepare("SELECT player_number, player1, player2, player3, player4, ".
								"played_now, game_status FROM games WHERE game_db = ?");
			$sth->execute(array($_SESSION["gameId"]));
		}catch(PDOException $e){
			server::return_fail($e);
		}
		$res = $sth->fetch(PDO::FETCH_ASSOC);
		if($res["played_now"] != 1){ 
			server::add("info","don't need update game_status");
			server::output();
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
	/**
	* Выход пользователя из игры. Происходит подчистка игры за ним
	* @return void
	*/
	public static function exit_player(){
		self::get_player($_SESSION["player_id"])->quit();
	}
}
?>
