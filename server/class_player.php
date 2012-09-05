<?php
/**
 * @author Dolgov mikhail
 * Данный класс нужен для работы с общей БД, для отдельной игры используется класс user_info.
 * В будущем оба класса будут объеденены.
 * @version 0.5
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
				$sth->execute($this->prepare_to_db());
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
								"WHERE players.player_id = :player_id");
			$sth->execute($this->prepare_to_db());
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
		$sql ="SELECT first_name, last_name, photo, photo_rec, ";
		$sql.="status, game_id FROM players WHERE player_id = ".$id;
		try{
			$sth = $db->prepare($sql);
			$sth->execute();
			$resault = $sth->fetch(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			server::return_fail($e);
		}		
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
	/**
	* Подчищаем информацию об игроке в бд
	*/ 
	public function quit(){
		$this->status = 0;
		$this->game_id = NULL;
		$this->update_in_db();
	}
	/**
	* Возвращает массив для ввода в БД
	* @return array
	*/
	private function prepare_to_db(){
		return array(
					    ":player_id"	=> $this->player_id,
					    ":first_name"=> $this->first_name,
					    ":last_name"	=> $this->last_name,
					    ":photo"		=> $this->photo,
					    ":photo_rec"	=> $this->photo_rec,
						":status"	=> $this->status,
						":game_id"	=> $this->game_id
					);
	}
}
