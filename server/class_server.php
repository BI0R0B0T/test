<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mikhail
 * Date: 03.07.12
 * Time: 15:30
 * To change this template use File | Settings | File Templates.
 * Типичный синглтон.
 */
class server{
    /**
     * Состояние выполнения запроса
     */
    private static $state = TRUE;
    /**
     * Тело ответа сервера
     */
    private static $responce = array();
    /**
     * Ввод данных на сервер.в виде строки в JSON формате
     * @static
     * @param string $request
     * @return void
     */
    public static function input($request){
        $req = json_decode($request);
        if(!$req){
            self::add("reason", "no data");
            self::return_fail();
        }
        self::check_require($req);
        switch ($req->comandCode){
            case 0: self::start_game($req->comand); break;
            case 1: self::stopgame() ; break;
            case 2: self::give_game(); break;
            case 3: self::exit_from_game(); break;
            case 4: self::open_cell($req->comand); break; //depricated
            case 5: self::display_game_list();break;
            case 6: self::drop_game($req->comand);break;
            case 7: self::open_game($req->comand);break;
            case 8: self::connect_game($req->comand);break;
            case 9: self::move_unit($req->comand);break;
            case 10: self::get_player_info($req->comand);break;
            default:
                    self::add("reason", "incorrect comand (0)");
                    self::return_fail();
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
    /**
     * Добавляет данные в ответ сервера
     * @static
     * @param string $field - имя поля в ответе сервера
     * @param mixed data - данные которые нужно добавить
     * @return void
     */
    public static function add($field, $data){
        if( in_array($field, array("map", "move", "unit"))){
            self::$responce[$field][] = $data;
        }else{
            self::$responce = $data;
        }
    }

    /**
     * Экстренное завршение работы сервера (неправильные входные параметры)
     * @static
     * @return void
     */
    public static function return_fail(){
        self::$state = FALSE;
        self::output();
    }
    /**
     * Проверяет правомерность запроса.
     * @static
     * @param object $require JSON object то что ввел пользователь
     * @return void
     * @version 0.3
     */
    private static function check_require($require){
        //Проверка на то все ли параметры на месте
        if(in_array($require->comandCode,array(0,4,6,7,8,9))){
            if(!isset($require->comand) or "" == $require->comand) {
                self::add("reason", "incorrect comand (1)");
                self::add("req", $require);
                self::return_fail();
            }
        }
        //Проверка на то играет ли данный пользователь впринципе
        if(!in_array($require->comandCode,array(0,8,5,6,10))){
            if(!isset($_SESSION["gameId"]) && is_null($_SESSION["gameId"])){
                self::add("reason", "incorrect comand (2)");
                self::add("req", $require);
                self::return_fail();
            }
        }
    }
    /**
     * Запускаем игру (происходит создание новой карты)
     * @param int $type код типа игры (1 - 1x1, 2 - 2x2, 3 - 4 игрока, каждый сам за себя)
     */
    private static function start_game($type){
        /**
         * Если игра уже создана
         */
        if(isset($_SESSION["gameId"]) && !is_null($_SESSION["gameId"])){
            game::convert_2_JSON($_SESSION["gameId"]);
        }else{
			$_SESSION["play"] = 1;
			$_SESSION["gameId"] = game::start_game($type);
        }

    }
    /**
     * Останавливаем игру (при этом игра удаляется))
     */
    private static function stopgame(){
        if(isset($_SESSION["gameId"]) && !is_null($_SESSION["gameId"])){
            game::stop_game($_SESSION["gameId"]);
            unlink("../db/".$_SESSION["gameId"].".db") ;
            $_SESSION["play"] = 0;
            $_SESSION["gameId"] = null;
            self::output();
        }else{
            self::return_fail();
        }
    }
    /**
     * Возвращает текущую игру
     */
    private static function give_game(){
        if(isset($_SESSION["gameId"]) && !is_null($_SESSION["gameId"])){
            game::convert_2_JSON($_SESSION["gameId"]);
        }else{
            self::return_fail();
        }
    }
    /**
     * Открываем клетку (открыта для тестирования, потом закрою)
     * @param int $cell_id
     *
     */
    private static function open_cell($cell_id){
        game::open_cell($_SESSION["gameId"],$cell_id);
    }
    /**
     * выход из игры (игра не удаляется)
     */
    private static function exit_from_game(){
        session_destroy();
        $_SESSION = array();
        $_COOKIE = array();
        self::output();
    }
    /**
     * выводит список доступных игр
     */
    private static function display_game_list(){
        gamelist::get_gamelist();
    }
    /**
     * Удаляет игру (будет доступна только для админа)
     * @param int $game_id
     *
     */
    private static function drop_game($game_id){
        game::stop_game($game_id);
        $_SESSION["play"] = 0;
        $_SESSION["gameId"] = null;
        self::output();
    }
    /**
     * просматриваем игру (Играть при этом нельзя)
     * @param int $game_id
     *
     */
    private static function open_game($game_id){
        $_SESSION["play"] = 0;
        $_SESSION["gameId"] = $game_id;
        self::give_game();
    }
    /**
     * Присоединяемся к игре (игрок может играть)
     * @param int $game_id
     * @version 0.2
     */
    private static function connect_game($game_id){
        if(isset($_SESSION["gameId"]) && $_SESSION["gameId"]){
            self::give_game();
        }else{
            if(gamelist::can_connect($game_id)){
                $_SESSION["play"] = 1;
                $_SESSION["gameId"] = $game_id;
                game::add_player();
            }else{
                self::add("reason", "You can't connect to this game :(");
                self::return_fail();
            }
        }
    }
    /**
     * Перемещение юнитов по карте
     * @param array $move
     * @version 0.2
     */
    private static function move_unit($move){
        $unit = unit::get_unit_from_db($move[0]);
        if($unit->checkPossibleMove()){
            $unit->move_to($move[1]);
        }else{
            self::return_fail();
        }
    }
	
	private static function get_player_info($id){
		if($id){
			
		}else{
			$player = new player($_SESSION);
			self::add("player", (array)$player);
			self::output();			
		}
	}
}
