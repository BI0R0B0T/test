<?php
class game_db{
	private static $db_connection = NULL;
	const ADR = "../db/";
	private function __construct($db_name){
		$db_name = self::ADR.$db_name;
		if(!file_exists($db_name)){
			copy(self::ADR."main.db", $db_name);
		}
		self::$db_connection = new SQlite3($db_name);
	}
	private function __clone(){ }
	function __destruct(){ }
	public static function db_conn($db_name){
		if(NULL == self::$db_connection){
			new game_db($db_name);
		}
		return self::$db_connection;
	}
	/**
	* Метод сохраняет изменения в ячейке карты в БД
	**/
	public static function change_cell($cell){
		
	}
	/**
	* Метод извлекаеи ячейку карты из БД
	**/
	public static function load_cell($cell){
		
	}
	/**
	* Метод записывает данные игровой единицы (пирата, корабля) в БД
	**/
	public static function save_unit($unit){
		
	}
	/**
	* Метод записывает измененные данные игровой единицы (пирата, корабля) в БД
	**/
	public static function change_unit($unit){
		
	}
	/**
	* Метод записывает измененные данные игрока в БД
	**/
	public static function change_player($player){
		
	}
	/**
	* Метод записывает данные игрока в БД
	**/
	public static function save_player($player){
		
	}
}
?>