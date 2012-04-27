<?php  
include_once "class_cells.php";
include_once "class_game_db.php";
/**
* Типичный синглетон хранящий объект карты
* @author M. Dolgov <dolgov@bk.ru>
* @version 0.1
**/  
  class map{
    static private $map = array();
	static private $map_id = NULL;
	/**
	* создает новую карту
	**/
	private function __construct(){
		/*
		* 0 - 40 пустые клетки
		* 1 - 3 стрелка вверх
		* 2 - 3 стрелка двунаправленная по диагонали
		* 3 - 3 стрелка по диагонали
		* 4 - 3 в четыре стороны по диагонали
		* 5 - 3 в четыре стороны (вверх, вниз и вправо)
		* 6 - 3 на СЗ, Восток и Юг
		* 7 - 3 стрелка двунаправленная влево-вправо
		* 8 - 2 кони
		* 9 - 5 вертушки на 2 хода
		* 10 - 4 вертушка на 3 хода
		* 11 - 2 вертушка на 4 хода
		* 12 - 1 вертушка на 5 ходов
		* 13 - 6 лед
		* 14 - 3 капкан
		* 15 - 2 пушка
		* 16 - 2 крепость
		* 17 - 1 аборигенка
		* 18 - 4 ром
		* 19 - 4 крокодил
		* 20 - 1 людоед
		* 21 - 2 воздушный шар
		* 22 - 1 самолет
		* 23 - 5 клад 1 монета
		* 24 - 5 клад 2 монеты
		* 25 - 3 клад 3 монеты
		* 26 - 2 клад 4 монеты
		* 27 - 1 клад 5 монет
		* 28 - 48 море
		* 29 - 4 корабль на море
		* 30 - 0 не открытая клетка
		*/
		$list_of_possible_cells = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1,1,1,2,2,2,3,3,3,4,4,4,5,5,5,6,6,6,7,7,7,8,8,9,9,9,9,9,10,10,10,10,11,11,12,13,13,13,13,13,13,14,14,14,15,15,16,16,17,18,18,18,18,19,19,19,19,20,21,21,22,23,23,23,23,23,24,24,24,24,24,25,25,25,26,26,27);
		shuffle($list_of_possible_cells);
		$sea = array(0,1,2,3,4,5,7,8,9,10,11,12,13,14,24,25,26,39,52,65,91,104,117,130,38,51,64,77,103,116,129,142,143,144,154,155,156,157,158,159,160,161,163,164,165,166,167,168);
		$ship = array(6, 78, 90, 162);
		$r = 0;
		for($i = 0; $i < 169; $i++){
			if(in_array($i, $sea)){
				self::$map[] = cells::new_cell(28,$i);
			}elseif(in_array($i, $ship)){
				self::$map[] = cells::new_cell(29,$i);
			}else{
				self::$map[] = cells::new_cell($list_of_possible_cells[$r++],$i);
			}
		}
		self::$map_id = rand(1,100).time().".db";
		$db = game_db::db_conn(self::$map_id);
		self::save_map($db);
//		$db->save_map(self::$map);
	} 
	private function __clone(){
		
	} 
	function __destruct(){
		
	}
	private function get_map_from_db(){
		
	}
	static function map_generate(){
		if(empty(self::$map)){
			new map();
		}
		return self::$map;
	}
	static function get_map(){
		if(empty(self::$map)){
			self::get_map_from_db();
		}
		return self::$map;
	}
	static function drop_map(){
		
	}
	/**
	* Метод записывает сгенерированную карту в БД
	**/
	private static function save_map($db){
		$values = "";
		foreach(self::$map as $cell){
			$cell->save_cell_in_db($db);
		}
	}
	/**
	* Метод извлекает карту из БД
	**/
	private static function load_map($map){
		
	}
  }
?>
