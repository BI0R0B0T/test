<?php
class game_db{
	private static $db_connection = NULL;
	const ADR = "../db/";
	private function __construct($db_name){
		$db_name = self::ADR.$db_name.".db";
		if(!file_exists($db_name)){
			if(isset($_SESSION["start"])) {
				copy(self::ADR."main.db", $db_name);
			}else{
				server::add("reason","no such file $db_name") ;
				server::return_fail();
			}
		}
		self::$db_connection = new SQlite3($db_name);
	}
	private function __clone(){ }
	function __destruct(){ }
	public static function db_conn($db_name = FALSE){
		if(NULL == self::$db_connection){
			if(!$db_name){$db_name = $_SESSION["gameId"];	}
			new game_db($db_name);
		}
		return self::$db_connection;
	}
	public static function check_error($sql){
		if(self::$db_connection->lastErrorCode() > 0){
			throw new Exception( self::$db_connection->lastErrorMsg()."\n sql: ".$sql );
		}
	}
}
?>