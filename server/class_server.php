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
//		game::get_ship(1);
        $req = json_decode($request);
        if(!$req){
             self::return_fail("no data");
        }
        self::check_require($req);
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
     * Выводит строку с результатом работы сервера
     * @static
     * @return void
     */
    public static function output(){
        self::$responce["status"] = self::$state?"OK":"FAIL";
        if(!isset(self::$responce["last_id"])&&self::$state&&!isset($_GLOBALS["dont_need_log"])){
			self::add("last_id",loger::get_last_id()); 
		}
        return self::$responce;
//		loger::log_analizer(loger::get_from(0)) ;	
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
     * Экстренное завршение работы сервера (неправильные входные параметры)
     * @static
	 * @param string $reason текст причины
     * @throw Exception
     * @return void
     */
    public static function return_fail($reason){
        self::$state = FALSE;
		self::add("reason", $reason);
        self::output();
        throw new Exception("failed");
    }
    /**
     * Проверяет правомерность запроса.
     * @static
     * @param object $require JSON object то что ввел пользователь
     * @return void
     * @version 0.3
     */
    private static function check_require($require){
        if(!is_object($require)){
            self::add("req", $require);
            self::return_fail("incorrect input data");
        }
        //Проверка на то все ли параметры на месте
        if(in_array($require->comandCode,array(0,4,6,7,8,9))){
            if(!isset($require->comand) or "" == $require->comand) {
                self::add("req", $require);
                self::return_fail("incorrect comand (1)");
            }
        }
        //Проверка на то играет ли данный пользователь впринципе
        if(!in_array($require->comandCode,array(0,8,5,6,10))){
            if(!isset($_SESSION["gameId"]) && is_null($_SESSION["gameId"])){
				self::add("req", $require);
                self::return_fail("incorrect comand (2)");
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
        	$_SESSION["start"] = TRUE;
			$_SESSION["status"] = 1;
			$_SESSION["gameId"] = game::start_game($type);
			self::output();
        }

    }
    /**
     * Останавливаем игру (при этом игра удаляется))
     */
    private static function stop_game(){
        if(isset($_SESSION["gameId"]) && !is_null($_SESSION["gameId"])){
            game::stop_game($_SESSION["gameId"]);
            if(unlink("../db/".$_SESSION["gameId"].".db")){
	            $_SESSION["status"] = 0;
	            $_SESSION["gameId"] = null;
	            self::output();
            }else{
 				self::return_fail("Can't drop file "."../db/".$_SESSION["gameId"].".db");
            }
        }else{
            self::return_fail("Incorrect game id in SESSION");
        }
    }
    /**
     * Возвращает текущую игру
     */
    private static function give_game(){
        if(isset($_SESSION["gameId"]) && !is_null($_SESSION["gameId"])){
            game::convert_2_JSON($_SESSION["gameId"]);
        }else{
            self::return_fail("Incorrect game id in SESSION");
        }
    }
    /**
     * Открываем клетку (открыта для тестирования, потом закрою)
     * @param int $cell_id
     */
    private static function open_cell($cell_id){
		self::add("cell",game::open_cell($_SESSION["gameId"],$cell_id));
		self::output();

    }
    /**
     * выход из игры (игра не удаляется)
     */
    private static function exit_from_game(){
		gamelist::exit_player();
		game::exit_player();
        session_destroy();
        $_SESSION = array();
        self::output();
    }
    /**
     * выводит список доступных игр
     */
    private static function display_game_list(){
//		self::add("run","display_game_list");
		$_GLOBALS["dont_need_log"] = TRUE;
        gamelist::get_gamelist();
    }
    /**
     * Удаляет игру (будет доступна только для админа)
     * @param int $game_id
     *
     */
    private static function drop_game($game_id){
        game::stop_game($game_id);
        $_SESSION["status"] = 0;
        $_SESSION["gameId"] = null;
        self::output();
    }
    /**
     * просматриваем игру (Играть при этом нельзя)
     * @param int $game_id
     *
     */
    private static function open_game($game_id){
        $_SESSION["status"] = 0;
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
                $_SESSION["status"] = 1;
                $_SESSION["gameId"] = $game_id;
                game::add_player();
            }else{
				self::return_fail("You can't connect to this game :(");
            }
        }
    }
    /**
     * Перемещение юнитов по карте
     * @param array $move
     * @version 0.3
     */
    private static function move_unit($move){
		$unit = game::get_unit($move[0]);
        if(game::checkPossibleMove()){
			//Проверяем возможен ли такой ход
			$prev_cell = game::get_cell($unit->position);
			if(!in_array($move[1],$prev_cell->possible_next_cells)){
				self::return_fail("imposible move from ".$unit->position." to ".$move[1]);
			}
			loger::save(3,json_encode(array("start_move")));        	
			//действие клеток на юниты
        	
        	//перемещение юнита
            $unit->move_to($move[1], TRUE);
        }else{
            self::return_fail("");
        }
    }
	
	private static function get_player_info($id = FALSE){
		if($id){
			//Выводит информацию по конкретному пользователю
			$player = game::get_player($id);
		}else{
			//Выводит информацию по текущему пользователю (чья сессия)
			$player = new player($_SESSION);
		}
		self::add("player", (array)$player);
		self::output();			
	}
	
	private static function get_game_status($game_id){
		$_SESSION["gameId"] = $game_id;
		self::add("game_status",gamelist::get_game_status());
		self::output();
	}
	/**
	* Выводит какие события произошли начиная с указанного события
	* @param int $from id с какого начиная выводятся логи
	*/
	private static function get_log($from){
		self::add("you_move",(game::who_next() == $_SESSION["player_id"])?1:0);
		server::add("log", loger::get_from((int)$from));
		self::output();
	}
	/**
	* Возвращает, что произошло с момента последнего хода
	* 
	* @param int $id последнего хода
	*/
	private static function game_update($id){
		if(0 >= $id){
			self::return_fail("Incorrect data ($id)");
		}
		$log = loger::get_from($id);
		self::add("log",$log);
		loger::log_analizer($log);
		self::output(); 
	}	
}
