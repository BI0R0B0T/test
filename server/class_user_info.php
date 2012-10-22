<?php
/**
* Информация о игроке
* @version 0.1
*/
class user_info
{
	public $user_id;
	public $first_name;		// Имя пользователя
	public $last_name;		// Фамилия пользователя
	public $photo;			// большая фотка пользователя
	public $photo_rec;		// уменьшеный аватар
	public $online;   
	public $played;
	public $player_number;	//номер пользователя на поле (1-4)  
	public $coins = 0;
	public $color;
	public $move_finished = TRUE;
	private static $color_array = array(1=>0xffffff, 2=>0xff0000, 3=>0x0000ff, 4=>0x000000);
	
	public function __construct($id, $first_name='', $last_name='', $photo='', $photo_rec='', $online=FALSE, $played = FALSE, $color = 0xff0000, $number = 1)
	{
		$this->user_id = $id;
		$this->first_name = $first_name;
		$this->last_name = $last_name;
		$this->photo = $photo;
		$this->photo_rec = $photo_rec;
		$this->online = $online;
		$this->played = $played;
		$this->color = $color;
		$this->player_number = $number;
	}
	/**
	* Сохраняет свежесозданного пользователя в БД
	*/
	public function save_in_db(){
		try{
			$sql = "SELECT player1_id, player2_id, player3_id, player4_id, game_type FROM games";
			$sth = game_db::db_conn()
					->query($sql);
			$sth->setFetchMode(PDO::FETCH_ASSOC);
			$res = $sth->fetch();
			if(!$res){
				$this->player_number = 1;
				$this->update_player_id_in_games();
			}else{
				$add = FALSE;
				if($res["game_type"] == 1){
					$max = 2;
				}else{
					$max = 4;
				}
				for($i=1; $i<=$max; $i++){
					if(!$res["player".$i."_id"]){
						$this->player_number = $i;
						$this->update_player_id_in_games();
						$add = TRUE;
						break;
					}
				}
				if(!$add){
					server::return_fail("Maximum $max players in game");
				}
			}
			$sql="INSERT INTO players(player_id,first_name,last_name,photo,photo_rec,coins, ".
				 "played, color, number) VALUES(:player_id, :first_name,:last_name,:photo,".
				  ":photo_rec, :coins, :played, :color, :number)";
			$sth = game_db::db_conn()
					->prepare($sql);
			$sth->bindParam(":player_id",$this->user_id);
			$sth->bindParam(":first_name",$this->first_name);
			$sth->bindParam(":last_name",$this->last_name);
			$sth->bindParam(":photo",$this->photo);
			$sth->bindParam(":photo_rec",$this->photo_rec);
			$sth->bindParam(":coins",$this->coins);
			$sth->bindParam(":played",$this->played);
			$sth->bindParam(":color",self::$color_array[$this->player_number]);
			$sth->bindParam(":number",$this->player_number);
			$sth->execute();
			return $this->player_number;
		}catch(PDOException $e){
			server::return_fail($e);
		}
	}
	/**
	* Обновляет информацию в таблице games
	*/
	public function update_player_id_in_games(){
		try{
			$sql = "UPDATE games SET player".$this->player_number."_id = :id WHERE id = 1";
			$sth = game_db::db_conn()
					->prepare($sql);
			$sth->bindParam(":id",$this->user_id);
			$sth->execute();
		}catch(PDOException $e){
			server::return_fail($e);
		}
	}
	public static function get_from_db($id){
		try{
			$sql =  "SELECT first_name,last_name,photo,photo_rec,coins,played,color,number ".
					"FROM players WHERE player_id = :id";
			$sth = game_db::db_conn()
					->prepare($sql);
			$sth->bindParam(":id",$id);
			$sth->execute();
			$sth->setFetchMode(PDO::FETCH_ASSOC);
			$res = $sth->fetch();
			return new user_info(	$res["player_id"],$res["first_name"],$res["last_name"],
									$res["photo"],$res["photo_rec"],TRUE,TRUE,$res["color"],
									$res["number"]);
		}catch(PDOException $e){
			server::return_fail($e);
		}
	}
	
	public function quit(){
		$this->played = FALSE;
		$this->plaer_number = 0;
	}
}