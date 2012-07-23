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
	* Возвращает объект логи начиная с указанного id
	*/
	public static function get_from($id){
		$db = game_db::db_conn();
		$sql = "SELECT log.id, log.text, log.type, log.who_add FROM log WHERE log.id > ".$id;
		$res = $db->query($sql);
		game_db::check_error($sql);
		server::add("log",$res->fetchArray(SQLITE3_ASSOC));
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
}
?>