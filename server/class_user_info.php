<?php
/**
* Информация о игроке
* @version 0.1
*/
class user_info
{
	private $user_id;
	public $first_name;		// Имя пользователя
	public $last_name;		// Фамилия пользователя
	public $photo;			// большая фотка пользователя
	public $photo_rec;		// уменьшеный аватар
	public $online;   
	public $played;
	public $player_number;	//номер пользователя на поле (1-4)  
	public $coins = 0;
	public $color;
	private static $color_array = array(1=>0xffffff, 2=>0xff0000, 3=>0x0000ff, 4=>0x000000);
	
	public function __construct($id, $first_name='', $last_name='', $photo='', $photo_rec='', $online=FALSE, $played = FALSE, $color = 0xff0000)
	{
		$this->user_id = $id;
		$this->first_name = $first_name;
		$this->last_name = $last_name;
		$this->photo = $photo;
		$this->photo_rec = $photo_rec;
		$this->online = $online;
		$this->played = $played;
		$this->color = $color;
	}
	/**
	* Сохраняет свежесозданного пользователя в БД
	*/
	public function save_in_db(){
		$db = game_db::db_conn();
		//Определим какой по номеру он будет
		$sql = "SELECT player1_id, player2_id, player3_id, player4_id FROM games";
		$res = $db->query($sql);
		game_db::check_error($sql);
		$res = $res->fetchArray(SQLITE3_ASSOC);
		if(!$res){
			$this->player_number = 1;
			$this->update_player_id_in_games();
		}else{
			$add = FALSE;
			for($i=1; $i<5; $i++){
				if(!$res["player".$i."_id"]){
					$this->player_number = 1;
					$this->update_player_id_in_games();
					$add = TRUE;
					break;
				}
			}
			if(!$add){
				server::add("reason","Maximum 4 players in game");
				server::return_fail();
			}
		}
		$sql="INSERT INTO players(id,player_id,first_name,last_name,photo,photo_rec,coins,played, color) VALUES(";
		$sql .="null,".$this->user_id.", \"".$this->first_name."\", \"".$this->last_name."\", \"".$this->photo;
		$sql .= "\", \"".$this->photo_rec."\", ".$this->coins.", 1,".self::$color_array[$this->player_number].")" ;
		$res = $db->query($sql);
		game_db::check_error($sql);
		return $db->lastInsertRowID();
	}
	
	public function update_player_id_in_games(){
		$db = game_db::db_conn();
		$sql = "UPDATE games SET player".$this->player_number."_id = ".$this->user_id." WHERE id = 1";
		$db->query($sql);
		game_db::check_error($sql);
	}
}
?>