<?php
/**
* ����� ��� ���������� � ������������� �������� �������
*/
class loger{
	private $time;
	private $type;
	/* ��������� �������� � �������� ���������� $type
		0 - ������ ����
		1 - ���������� ������ ������
		2 - ����� ������ �� ���� 
		3 - ����� �������� ���
		4 - ����������� ������� �� ����
	*/
	private $text;
	private $user_id;
	/**
	* ������� ����� ��������� ������ �������
	* @param int $type - ��� ������
	* @param string $text ����� ������ � ������� JSON
	* @param int $id id ������������
	*/
	private function __construct($type, $text, $id){
		$this->time = microtime();
		$this->type = $type;
		$this->text = $text;
		$this->user_id = $id;
	}
	/**
	* ���������� ������ ������ � ��
	* @param int $type - ��� ������
	* @param string $text ����� ������ � ������� JSON
	* @param int $id id ������������
	*/
	public static function save($type, $text, $id){
		$log = new loger($type, $text, $id);
		$db = game_db::db_conn($_SESSION["gameId"]);
		$sql = "INSERT INTO log('time','type','text','who_add') VALUES ('now', ";
		$sql.= $log->type.", '";
		$sql.= $log->text."', ";
		$sql.= $log->user_id.")";
		$db->query($sql);
	}
	/**
	* ���������� ������ ���� ������� � ���������� �������
	*/
	public function get_from($time){
		
	}
	/**
	* ���������� Id ���������� ��������� ������
	* @return int
	*/
	public static function who_was_last(){
		$db = game_db::db_conn($_SESSION["gameId"]);
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