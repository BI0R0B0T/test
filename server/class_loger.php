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
	* @param int $id id пользователя
	*/
	public static function save($type, $text, $id){
		$log = new loger($type, $text, $id);
		$db = game_db::db_conn();
		$sql = "INSERT INTO log('time','type','text','who_add') VALUES ('now', ";
		$sql.= $log->type.", '";
		$sql.= $log->text."', ";
		$sql.= $log->user_id.")";
		$db->query($sql);
	}
	/**
	* Возвращает объект логи начиная с указанного времени
	*/
	public function get_from($time){
		
	}
	/**
	* Возвращает Id последнего ходившего игрока
	* @return int
	*/
	public static function who_was_last(){
		$db = game_db::db_conn();
		$sql = "SELECT log.who_add FROM log WHERE log.type = 3 ORDER BY log.'time' DESC LIMIT 1";
		$res = $db->query($sql);
		$later = $res->fetchArray(SQLITE3_ASSOC);
		if($later){
			return (int)$later["who_add"];
		}else{
			return 0;
		}
	}
}
?>