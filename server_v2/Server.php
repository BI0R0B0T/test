<?php
namespace piraty;
/**
 * User: Mikhail Dolgov
 * Date: 02.01.13
 * Time: 9:27
 */
require_once("../server/config.php");
// Читаем данные, переданные в POST
//$rawPost = file_get_contents('php://input');
//to test only
$rawPost = $_GET["q"];
// Заголовки ответа
date_default_timezone_set("Europe/Moscow");
header('Content-type: text/plain; charset=utf-8');
header('Cache-Control: no-store, no-cache');
header('Expires: ' . date('r'));
header('Last-Modified: ' . date('r'));
try{
	Server::input($rawPost);
}catch(\Exception $e){
	Server::return_fail($e->getMessage());
}
class Server{
	/**
	 * Состояние выполнения запроса
	 */
	private static $state = TRUE;
	/**
	 * Тело ответа сервера
	 */
	private static $responce = array();
	public static function input($request){
		$req = json_decode($request);
		if(!$req){
			self::return_fail("no data");
		}
//		self::check_require($req);
		switch ($req->comandCode){
			case 0: self::start_game($req->comand); break;
			case 1: self::stop_game() ; break;
			case 2: self::give_game(); break;
			case 3: self::exit_from_game(); break;
			case 4: self::open_cell($req->comand); break; //depricated
			case 5: self::display_game_list();break;
			case 6: self::drop_game($req->comand);break;
			case 7: self::open_game($req->comand);break;
			case 8: self::connect_game($req->comand);break;
			case 9: self::move_unit($req->comand);break;
			case 10: self::get_player_info($req->comand);break;
			case 11: self::get_game_status($req->comand);break;
			case 12: self::get_log($req->comand);break;
			case 13: self::game_update($req->comand); break;
			default:
				self::return_fail("incorrect comand (0)");
		}
	}

	/**
	 * Экстренное завршение работы сервера (неправильные входные параметры)
	 * @static
	 * @param string $reason текст причины
	 * @return void
	 */
	public static function return_fail($reason){
		self::$state = FALSE;
		self::add("reason", $reason);
		self::output();
	}
	/**
	 * Добавляет данные в ответ сервера
	 * @static
	 * @param string $field - имя поля в ответе сервера
	 * @param mixed $data - данные которые нужно добавить
	 * @return void
	 */
	public static function add($field, $data){
		switch ($field){
			case "unit":    self::$responce["units"][] = $data; break;
			case "move_list": self::$responce["move_list"][] = $data; break;
			case "cell": self::$responce["map"][] = $data; break;
			default:  self::$responce[$field] = $data;
		}
	}
	/**
	 * Выводит строку с результатом работы сервера
	 * @static
	 * @return void
	 */
	public static function output(){
		self::$responce["status"] = self::$state?"OK":"FAIL";
		printf(json_encode(self::$responce));
		exit;
	}
}

