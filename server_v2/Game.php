<?php
namespace piraty;
/**
 * User: Mikhail Dolgov
 * Date: 02.01.13
 * Time: 10:01
 */
use piraty;
class Game{
	/**
	 * Массив с полем игры
	 * @var array
	 */
	private $map = array();
	/**
	 * массив с юнитами
	 * @var array
	 */
	private $units = array();
	/**
	 * Массив с игроками
	 * @var array
	 */
	private $players = array();
	/**
	 * Ссыдка на объект
	 * @var null|Game
	 */
	private static $game = null;

	/**
	 * Для работы синглтона
	 */
	private function __construct(){	}

	/**
	 * Возвращает экземпляр класса
	 * @return null|Game
	 * @static
	 */
	public static function getGame(){
		if(!self::$game){
			self::$game = new Game();
		}
		return self::$game;
	}

	/**
	 * Генерит новую игру
 	 */
	public static function  newGame(){
		self::$game = new Game();
		$g = self::$game;
		$_SESSION["map"] = $g->map;
	}

	private function saveGame(){

	}

	public function createResponse(){
		$list = array("map");
		$key_list = array("cell");
		foreach($list as $i=>$l){
			foreach($this->$l as $k=>$v){
				if($_SESSION[$l][$k] != (string)$v){
					Server::add($key_list[$k],$v);
				}
			}
		}
	}
}
