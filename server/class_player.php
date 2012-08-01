<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mikhail
 * Date: 29.06.12
 * Time: 17:20
 * To change this template use File | Settings | File Templates.
 * Данный класс нужен для работы с общей БД, для отдельной игры используется класс user_info.
 * В будущем оба класса будут объеденены.
 * @version 0.4
 */
class player
{
    public $player_id;
    public $first_name;
    public $last_name;
    public $photo;
    public $photo_rec;
	public $status;
	public $game_id;
    public function __construct($add){
        $this->player_id = $add["player_id"];
        $this->first_name= is_null($add["first_name"])?"no":$add["first_name"];
        $this->last_name = is_null($add["last_name"])?"no":$add["last_name"];
        $this->photo     = is_null($add["photo"])?"../pic/no_photo.png":$add["photo"];
        $this->photo_rec = is_null($add["photo_rec"])?"../pic/no_photo.png":$add["photo_rec"];
    }
	/**
	* Сохраняет информацию об игроке в БД game_stat
	*/
	public function add_in_db(){
		try{
			$db = game_stat::get_db();
			$sth = $db->prepare("SELECT  count() AS count FROM players WHERE player_id = ?");
			$sth->execute(array($_SESSION["player_id"]));
			$sth->setFetchMode(PDO::FETCH_ASSOC);
			$usr = $sth->fetch();
			if($usr["count"]){
				$this->update_in_db();
			}else{
				$sth = $db->prepare("INSERT INTO players(player_id,first_name,last_name,photo,".
							"photo_rec,status,game_id) VALUES(:player_id, :first_name, ".
							":last_name,:photo,:photo_rec,:status,:game_id)");
				$sth->execute((array)$this);
			}
		}catch(PDOException $e){
			server::return_fail($e);
		}
	}
	/**
	* Обновляет информацию об игроке в БД game_stat
	*/
	public function update_in_db(){
		try{
			$db = game_stat::get_db();
			$sth = $db->prepare("UPDATE players SET ".
									"first_name = :first_name, ".
									"last_name = :last_name, ".
									"photo = :photo, ".
									"photo_rec = :photo_rec, ".
									"status = :status, ".
									"game_id = :game_id ". 
								"WHERE players.player_id = player_id");
			$sth->execute((array)$this);
		}catch(PDOException $e){
			server::return_fail($e);
		}		
	}
	/**
	* @return void
	*/
	public function add_in_session(){
		$_SESSION["player_id"]	= $this->player_id;
		$_SESSION["first_name"]	= $this->first_name;
		$_SESSION["last_name"]	= $this->last_name;
		$_SESSION["photo"]		= $this->photo;
		$_SESSION["photo_rec"]	= $this->photo_rec;
		$_SESSION["SID"]		= session_id();
		$_SESSION["status"]		= 0;
		$_SESSION["gameId"]		= null;
 	}
	public static function get_from_db($id){
		$db = game_stat::get_db();
		$sql = "SELECT players.first_name, players.last_name, players.photo, players.photo_rec, ";
		$sql.= "players.status, players.game_id FROM players WHERE players.id = ".$id;
		$resault = $db->query($sql);
		game_stat::check_error($sql);
		$resault = $resault->fetchArray(SQLITE3_ASSOC);
		$resault["player_id"] = $id;
		return new player($resault);
	}
	/**
	* ОБновление свойст пользователя 
	* @param array $property
	* @return void
	* @version 0.3
	*/
	public function update_property($property){
		foreach($property as $k=>$v){
			if($k == "player_id"){ continue;}
			$this->$k = $v;
		}
		$this->update_in_db();
	}

    /**
    * Вырезает из GET запроса информацию о игроке
    * @return object player
    */
	public static function new_from_get(){
		$add = explode("?",$_GET["uid"]);
		$count = count($add);
		$add["player_id"] = $add[0];
		for($i = 1; $i<$count; $i++){
			$v = explode("=", $add[$i]);
			$add[$v[0]] = $v[1];
		}
		return new player($add);
	}
	
}
