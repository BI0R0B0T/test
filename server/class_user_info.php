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
		$db = game_db::db_conn();
		//Определим какой по номеру он будет
		$sql = "SELECT player1_id, player2_id, player3_id, player4_id, game_type FROM games";
		$res = $db->query($sql);
		game_db::check_error($sql);
		$res = $res->fetchArray(SQLITE3_ASSOC);
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
		$sql="INSERT INTO players(player_id,first_name,last_name,photo,photo_rec,coins,played, color, number) VALUES(";
		$sql .=$this->user_id.", \"".$this->first_name."\", \"".$this->last_name."\", \"".$this->photo;
		$sql .= "\", \"".$this->photo_rec."\", ".$this->coins.", 1,".self::$color_array[$this->player_number].",".$this->player_number.")" ;
		$res = $db->query($sql);
		game_db::check_error($sql);
		return $this->player_number;
	}
	/**
	* Обновляет информацию в таблице games
	*/
	public function update_player_id_in_games(){
		$db = game_db::db_conn();
		$sql = "UPDATE games SET player".$this->player_number."_id = ".$this->user_id." WHERE id = 1";
		$db->query($sql);
		game_db::check_error($sql);
	}
	public static function get_from_db($id){
		$db = game_db::db_conn();
		$sql = "SELECT first_name,last_name,photo,photo_rec,coins,played,color,number FROM players WHERE player_id = ".$id;
		$res = $db->query($sql);
		game_db::check_error($sql);
		$res = $res->fetchArray(SQLITE3_ASSOC);
		return new user_info($id,$res["first_name"],$res["last_name"],$res["photo"],$res["photo_rec"],TRUE,TRUE,$res["color"],$res["number"]);
	}
	public static function get_from_db_by_number($number){
		$db = game_db::db_conn();
		$sql = "SELECT player_id,first_name,last_name,photo,photo_rec,coins,played,color, number FROM players WHERE played = 1 and number = ".$id;
		$res = $db->query($sql);
		game_db::check_error($sql);
		$res = $res->fetchArray(SQLITE3_ASSOC);
		return new user_info($res["player_id"],$res["first_name"],$res["last_name"],$res["photo"],$res["photo_rec"],TRUE,TRUE,$res["color"],$res["number"]);
	}
}
?>