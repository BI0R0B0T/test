<?php
/**
* Синглтон для связи с базой игры
* новая версия переехала на PDO
* @version 1.0
*/
class game_db{
	private static $db_connection = NULL;
	const ADR = "../db/";
	/**
	* Подключение к БД с игрой
	* @param string $db_name необязательный параметр. Используется только при создании новой игры
	* @return PDO object
	*/  
	public static function db_conn($db_name = FALSE){
		if(NULL == self::$db_connection){
			if(!$db_name){$db_name = $_SESSION["gameId"];	}
			$db_name = self::ADR.$db_name.".db";
			if(!file_exists($db_name)){
				if(isset($_SESSION["start"])) {
					copy(self::ADR."main.db", $db_name);
				}else{
					server::return_fail("no such file $db_name");
				}
			}
			try{
				self::$db_connection = new PDO("sqlite:".$db_name);
				self::$db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}catch(PDOException $e){
				server::return_fail($e->getMessage());
			}
		}
		return self::$db_connection;
	}
}