<?php
class user_info
{
	private $user_id;
	public $first_name;		// Имя пользователя
	public $last_name;		// Фамилия пользователя
	public $photo;		
	public $photo_rec;		
	public $online;   
	public $played;
	public $plaer_number;  
	public $coins = 0;
	
	public function __construct($id, $first_name='', $last_name='', $photo='', $photo_rec='', $online=FALSE, $played = FALSE)
	{
		$this->user_id = $id;
		$this->first_name = $first_name;
		$this->last_name = $last_name;
		$this->photo = $photo;
		$this->photo_rec = $photo_rec;
		$this->online = $online;
		$this->played = $played;
	}
	/**
	* Сохраняет свежесозданного пользователя в БД
	*/
	public function save_in_db(){
		$db = game_db::db_conn($_SESSION["gameId"]);
		$sql  = "INSERT INTO players(id,player_id, first_name, last_name, photo, photo_rec, coins) VALUES(";
		$sql .="null,".$this->user_id.", \"".$this->first_name."\", \"".$this->last_name."\", \"".$this->photo;
		$sql .= "\", \"".$this->photo_rec."\", ".$this->coins.")" ;
		$res = $db->query($sql);
		return $db->lastInsertRowID();
	}
}
?>