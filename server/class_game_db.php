<?php
class game_db{
	private static $db_connection = NULL;
	const ADR = "../db/";
	private function __construct($db_name){
		$db_name = self::ADR.$db_name.".db";
		if(!file_exists($db_name)){
			copy(self::ADR."main.db", $db_name);
		}
		self::$db_connection = new SQlite3($db_name);
	}
	private function __clone(){ }
	function __destruct(){ }
	public static function db_conn($db_name = FALSE){
		if(!$db_name){
			$db_name = $_SESSION["gameId"];
		}
		if(NULL == self::$db_connection){
			if(!$db_name){return FALSE;	}
			new game_db($db_name);
		}
		return self::$db_connection;
	}
	public static function check_error(){
		var_dump($_SESSION);
		var_dump(self::$db_connection);
//		if($this->lastErrorCode() > 0){
//			throw new Exception( $this->lastErrorMsg() );
//		}
	}
}
?>