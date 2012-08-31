<?php
/**
* Синглтон для создания подключения к базе данных со статистикой всех игр
*/
class game_stat{
	private static $game_db = NULL;
	public static function get_db(){
		if(!self::$game_db){
//			self::$game_db = new SQLite3(DBNAME);
			try{
				self::$game_db = new PDO("sqlite:".DBNAME);
			}catch(PDOException $e){
				server::return_fail($e->getMessage());
			}
		}
		return self::$game_db;
	}
	public function __construct(){} 
	public static function check_error($sql){
		server::add("sql",$sql);
		return;
		if(self::$game_db->lastErrorCode() > 0){
			throw new Exception( self::$game_db->lastErrorMsg()." sql: ".$sql );
		}
	}
}
?>
