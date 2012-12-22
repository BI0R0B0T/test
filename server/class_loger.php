<?php
/**
* Класс для сохранения и синхронизации действия игроков
*/
class loger{
	private $time;
	private $type;
	/* Возможные значения и описание переменной $type
		0 - начало игры
		1 - добавление нового игрока
		2 - выход игрока из игры 
		3 - игрок начинает ход
		4 - перемещение пиратов на поле
		5 - игрок завершил ход
		6 - юнит умер (совсем умер)
		7 - юнита убили (юнит воскресает на корабле)
		8 - юнита воскресает (юнит воскресает в шатре амазонки)
	*/
	private $text;
	private $user_id;
	/**
	* Создаем новый экземпляр класса логгера
	* @param int $type - тип записи
	* @param string $text текст записи в формате JSON
	* @param int $id id пользователя
	*/
	private function __construct($type, $text, $id){
		$this->time = microtime();
		$this->type = $type;
		$this->text = $text;
		$this->user_id = $id;
	}
	/**
	* Сохранение данной записи в БД
	* @param int $type - тип записи
	* @param string $text текст записи в формате JSON
	* @return void
	*/
	public static function save($type, $text){
		$log = new loger($type, $text, $_SESSION["player_id"]);
		$db = game_db::db_conn();
		$sql = "INSERT INTO log('id','text','type','who_add') VALUES (null, '";
		$sql.= $log->text."', ";
		$sql.= $log->type.", ";
		$sql.= $log->user_id.")";
		$db->query($sql);
		game_db::check_error($sql);
		server::add("last_id",$db->lastInsertRowId());
	}
	/**
	* Возвращает логи начиная с указанного id в виде массива
	* @return array 
	*/
	public static function get_from($id){
		$db = game_db::db_conn();
		$sql = "SELECT log.id, log.text, log.type, log.who_add FROM log WHERE log.id > ".$id;
		$res = $db->query($sql);
		game_db::check_error($sql);
		$resault = array();
		while($a = $res->fetchArray(SQLITE3_ASSOC)){
			$resault[] = $a;
		}
		return $resault;
	}
	/**
	* Возвращает Id последнего ходившего игрока
	* @return int
	*/
	public static function who_was_last(){
		$db = game_db::db_conn();
		$sql = "SELECT log.who_add FROM log WHERE log.type = 5 ORDER BY log.'id' DESC LIMIT 1";
		$res = $db->query($sql);
		game_db::check_error($sql);
		$later = $res->fetchArray(SQLITE3_ASSOC);
		if($later){
			return (int)$later["who_add"];
		}else{
			return 0;
		}
	}
	
	public static function log_analizer($log){
		$return = array();
		foreach($log as $v){
			$type = $v["type"];
			if(!in_array($type, array(4))){ continue; }
			$txt = (array)json_decode($v["text"]);
			$map = array();
			foreach($txt as $k=>$move){
				foreach($move as $cell){
					if(in_array($cell, $map)){continue;}
					$map[] = $cell;
					server::add("cell",game::get_cell($cell));
				}
			}
		}
		server::add("units", game::get_units());
	}
	
	public static function get_last_id(){
		try{
			$sql = "SELECT id FROM log ORDER BY id DESC LIMIT 1";
			$sth = game_db::db_conn()
					->query($sql);
			$sth->setFetchMode(PDO::FETCH_ASSOC);
			$later = $sth->fetch();
			if($later){
				return (int)$later["id"];
			}else{
				return 0;
			}
		}catch(PDOException $e){
			server::return_fail($e);
		}
	}
}
?>