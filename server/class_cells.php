<?php
/**
* Паттерн фабрика по созданию новой клетки
* @author M.Dolgov <dolgov@bk.ru>
**/
abstract class cells{
	public $cell_id;
    public $type;
	public $rotate;
	public $can_stay_here = TRUE;
	public $auto_move = FALSE;
	public $coins_count = 0;
	public $ship_there = FALSE;
	public $open = FALSE;
	protected $possible_move = array();//массив возможных перемещений, каждое перемещение это координаты (x,y)
	public $possible_next_cells = array();
	private $count = 0; //Сколько раз пользователь был на этой клетке за ход
	/**
	* @desc на основании типа вызывает генерацию нового объекта данного типа
	* @param int $type
	* @param int $cell_id
	* @param boolean $add_info
	* @return object
	*/
  	public static function new_cell($type, $cell_id, $add_info = TRUE){
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
		switch($type){
			case 0: $return =  new empty_cell();break;
			case 1: $return =  new move_up();break;
			case 2: $return =  new strelka_dv_po_diag() ;break;
			case 3: $return =  new strelka_po_diag();break;
			case 4: $return =  new strelka_vo_vse_po_diag();break;
			case 5: $return =  new strelka_up_d_l_r();break;
			case 6: $return =  new strelka_ne_w_s();break;
			case 7: $return =  new strelka_l_r();break;
			case 8: $return =  new horses();break;
			case 9: $return =  new whirligig_2();break;
			case 10: $return =  new whirligig_3();break;
			case 11: $return =  new whirligig_4();break;
			case 12: $return =  new whirligig_5();break;
			case 13: $return =  new ice();break;
			case 14: $return =  new catcher();break;
			case 15: $return =  new gun();break;
			case 16: $return =  new fort();break;
			case 17: $return =  new aborigenka();break;
			case 18: $return =  new rom();break;
			case 19: $return =  new crocodille();break;
			case 20: $return =  new cannibal();break;
			case 21: $return =  new aerostat();break;
			case 22: $return =  new airplane();break;
			case 23: $return =  new storage_1();break;
			case 24: $return =  new storage_2();break;
			case 25: $return =  new storage_3();break;
			case 26: $return =  new storage_4();break;
			case 27: $return =  new storage_5();break;
			case 28: $return =  new sea();break;
			case 29: $return =  new ship();break;
			default: $return =  new closed();
		}
		if($add_info){
	        $return->cell_id = $cell_id;
			$return->add_same_info($type);
		}
		return $return;
	}
	public function cell_to_str(){
		if(is_a($this,"ship")){
			$is_ship = 1;
			$rotate = array(6=>0,78=>1,162=>2,90=>3);
			$this->rotate = $rotate[$this->cell_id];
			$open = 1;
		}elseif(is_a($this,"sea")){ 
			$is_ship = 0;
			$open = 1;
		}else{
			$is_ship = 0;
			$open = 0;
		}
		return implode(",", array($this->cell_id,$this->type,$this->rotate, 
						$this->can_stay_here?1:0, $open, $this->coins_count, $is_ship));
	}
	public function save_cell_in_db(){
		$db = game_db::db_conn();
		$sql = "INSERT INTO map( cell_id, type, rotate, can_stay_here, open, coins_count,"; 
		$sql.= "ship_there) VALUES (:cell_id, :type, :rotate, :can_stay_here, :open, ";
		$sql.= ":coins_count, :ship_there)";
		try{
			$sth = $db->prepare($sql);
			$sth->execute($this->prepare());
		}catch(PDOException $e){
			server::return_fail($e);
		}
	}
	/**
	* Возвращает объект cells из БД
	* @param int $id
	* @return object
	* @version 0.3
	*/
	public static function get_cell_from_db($id){
		$db = game_db::db_conn();
		try{
			$sth = $db->prepare("SELECT  map.cell_id, map.type, map.rotate, map.can_stay_here, "
								."map.open, map.coins_count, map.ship_there FROM map "
								."WHERE map.cell_id = :cell_id");
			$sth->bindParam(":cell_id",$id,PDO::PARAM_INT);
			$sth->execute();
		}catch(PDOException $e){
			server::return_fail($e);
		}
		$res = $sth->fetch(PDO::FETCH_ASSOC);
		if(1 == $res['open']){
			$new_cell = self::new_cell($res['type'],$res['cell_id'],TRUE);
			$new_cell->rotate = $res['rotate'];
			$new_cell->can_stay_here = $res["can_stay_here"]==1?TRUE:FALSE;
			$new_cell->coins_count = $res['coins_count'];
			$new_cell->set_possible_next_cells($res['cell_id']);
		}else{
			$new_cell = self::new_cell(30,$res['cell_id'],TRUE);
		}
		return $new_cell;
	}
	/**
	* Открывает закрытую клетку
	* @param int $id 
	* @return cells
	* @version 0.2
	*/
	public static function open_cell($id){
		$db = game_db::db_conn();
		try{
			$sth = $db->prepare("UPDATE map SET open = 1 WHERE map.cell_id = :cell_id");
			$sth->bindParam(":cell_id",$id,PDO::PARAM_INT);
			$sth->execute();
		}catch(PDOException $e){
			server::return_fail($e);
		}
		return self::get_cell_from_db($id);
	}
	protected function add_same_info($type){
		$this->rotate = (int)round(rand(0,3));
		$this->type = $type;
	}
	private function __construct(){}
	protected function set_possible_next_cells($cell_id){
		$row = floor($this->cell_id/13);
		$column = $this->cell_id%13;
		if(is_a($this, "gun")){
			//для пушек
			$move = $this->possible_move[0];
			$this->rotate_move($move, $this->rotate);
			if(0 == $move[0]){
				$this->possible_next_cells[0] = $move[1]>0?(int)(12*13+$column):(int)$column;
			}else{
				$this->possible_next_cells[0] = $move[0]>0?(int)($row*13+12):(int)($row*13);
			}
		}elseif(is_a($this, "sea")){
			//по морю
			if($cell_id < 13 || $cell_id>=156){
				if(isset($this->possible_move[$cell_id])){
					foreach($this->possible_move[$cell_id] as $id){
						$this->possible_next_cells[] = $id;
					}
				}
				if($cell_id!=12 && $cell_id != 168){
					$this->possible_next_cells[] = $cell_id+1;
				}
				if($cell_id != 0 && $cell_id != 156){
					$this->possible_next_cells[] = $cell_id-1;
				}
			}elseif(0 == $column || 12 == $column ){
				if(isset($this->possible_move[$cell_id])){
					foreach($this->possible_move[$cell_id] as $id){
						$this->possible_next_cells[] = $id;
					}
				}
				$this->possible_next_cells[] = $cell_id+13;
				$this->possible_next_cells[] = $cell_id-13;
			}else{
				if(isset($this->possible_move[$cell_id])){
					foreach($this->possible_move[$cell_id] as $id){
						$this->possible_next_cells[] = $id;
					}
				}
			}
		}elseif(is_a($this, "ship")){
			//с корабля
			if($cell_id < 13 || $cell_id>=156){
				if($cell_id != 2 && $cell_id != 158){
					$this->possible_next_cells[] = $cell_id - 1;
				}
				if($cell_id != 10 && $cell_id != 166){
					$this->possible_next_cells[] = $cell_id + 1;
				}
				if($cell_id <13){ $this->possible_next_cells[] = $cell_id+13; }
				if($cell_id >156){$this->possible_next_cells[] = $cell_id-13; }
			}else{
				if($cell_id != 26 && $cell_id != 38){
					$this->possible_next_cells[] = $cell_id - 13;
				}
				if($cell_id != 130 && $cell_id != 142){
					$this->possible_next_cells[] = $cell_id + 13;
				}
				if(0 == $column){ $this->possible_next_cells[] = $cell_id+1; }
				if(12 == $column){$this->possible_next_cells[] = $cell_id-1; }
			}
		}else{
			//остальные клетки
			foreach($this->possible_move as $move){
	 			$this->rotate_move($move,$this->rotate);
				$next_col = (int)$move[0]+(int)$column;
				$next_row = (int)$move[1]+(int)$row;
				if($next_col < 0 || $next_col > 12) {continue;}
				if($next_row < 0 || $next_row > 12) {continue;}
				$this->possible_next_cells[] = (int)(floor($next_row*13)+$next_col);
			}
		}
 		unset($this->possible_move);
	}
	/**
	* Изменяет значение возможных движений в зависимости от угла поворота клетки. Повороты против часовой стрелки
	**/
	private function rotate_move(&$move, $rotation){
		switch ($rotation){
			case 0: break;
			case 1: $move = $this->rotate_on_pi($move,TRUE); break;
			case 2: $move = array(-$move[0], -$move[1]); break;
			case 3: $move = $this->rotate_on_pi($move,FALSE); break;
		}
	}
	private function rotate_on_pi($coordinate, $left){
		$x = $coordinate[0];
		$y = $coordinate[1];
		if(!$x || !$y){
			if(0 == $x){
				$coordinate = $left?array($y,$x):array(-$y,$x);
			}else{
				$coordinate = $left?array($y, -$x):array($y,$x);
			}
		}else{
			$c = $x*$y;
			//определяем четверть
			if($c < 0){
				//1 or 3
				$coordinate = $left?array(-$y,$x):array($y,-$x);
			}else{
				//2 or 4
				$coordinate = $left?array($y,-$x):array(-$y,$x);
			}
		}
		return $coordinate;
	}
	public function update_info_in_db(){
		$db = game_db::db_conn();
		try{
			$sth = $db->prepare("UPDATE map SET type = :type, rotate = :rotate, "
								."can_stay_here = :can_stay_here, open = :open, "
								."coins_count = :coins_count, ship_there = :ship_there "
								."WHERE map.cell_id = :cell_id");
			$sth->execute($this->prepare());
		}catch(PDOException $e){
			server::return_fail($e);
		}
	}
	/**
	* Подготавливает объект для внесения его в БД
	* @return array
	* @version 0.1
	*/
	private function prepare(){
		$cell = array();
		$cell["cell_id"] = $this->cell_id;
		$cell["type"] = $this->type;
		$cell["rotate"] = $this->rotate;
		$cell["can_stay_here"] = (int)$this->can_stay_here;
		$cell["open"] = (int)$this->open;
		$cell["coins_count"] = $this->coins_count;
		$cell["ship_there"] = (int)$this->ship_there;
		return $cell;
	}
	/**
	* Действие которое происходит когда юнит приходит на клетку
	* 
	* @param object $unit 
	*/
	public function move_in($unit){
		$this->count++;
		//Тут будет вызов метода взаимодействия с юнитами на этой клетке
	}
	/**
	* Действие которое происходит когда пользователь уходит с клетки
	* 
	* @param object $unit
	*/
	public function move_out($unit){}
	/**
	* Действие которое происходит когда юнит стоит на клетке (в начале хода пользователя)
	* 
	* @param object $unit
	*/
	public function stand_action($unit){}
}

class automove_cell extends cells{
	public $can_stay_here = FALSE;
	public $auto_move = TRUE;
	public function move_in($unit){
		parent::move_in($unit);
		if(1==count($this->possible_next_cells)){
			$prev_return = $unit->move_to($this->possible_next_cells[0], FALSE);
		}else{
			$player = game::get_player($_SESSION["player_id"]);
			$player->move_finished = FALSE;
		}
	}
}
class singlestep extends cells{
	//Все клетки с возможным движением на одну клетку в любую сторону
	protected $possible_move = array(
										array(0,1),
										array(0,-1),
										array(1,-1),
										array(1,0),
										array(1,1),
										array(-1,-1),
										array(-1,0),
										array(-1,1)
									);
}

class empty_cell extends singlestep{
	//0 - 40 пустые клетки
	function __construct(){
    	return($this);
	}
	private function __clone(){}
}
class move_up extends automove_cell{
	//1 - 3 стрелка вверх
	function __construct(){
		$this->possible_move = array(array(0,-1));
		return($this);
	}
}
// 2 - 3 стрелка двунаправленная по диагонали
class strelka_dv_po_diag extends automove_cell{
	function __construct(){
		$this->possible_move = array(array(1,-1), array(-1,1));
		return($this);
	}
}
class strelka_po_diag extends automove_cell{
	//3 - 3 стрелка по диагонали
	function __construct(){
		$this->possible_move = array(array(1,-1));
		return($this);
	}
}
class strelka_vo_vse_po_diag extends automove_cell{
	//4 - 3 в четыре стороны по диагонали  
	function __construct(){
		$this->possible_move = array(array(1,-1),array(-1,-1),array(-1,1),array(1,1));
		return($this);
	}
}
class strelka_up_d_l_r extends automove_cell{
	//5 - 3 в четыре стороны (вверх, вниз, вдево и вправо)  
	function __construct(){
		$this->possible_move = array(array(1,0),array(-1,0),array(0,1),array(0,-1));
		return($this);
	}
}
class strelka_ne_w_s extends automove_cell{
	//6 - 3 на СЗ, Восток и Юг 
	function __construct(){
		$this->possible_move = array(array(-1,-1),array(1,0),array(0,1));
		return($this);
	}
}
class strelka_l_r extends automove_cell{
	//7 - 3 стрелка двунаправленная влево-вправо
	function __construct(){
		$this->possible_move = array(array(-1,0),array(1,0));
		return($this);
	}
}
class horses extends automove_cell{
	//7 - 3 стрелка двунаправленная влево-вправо
	function __construct(){
		$this->possible_move = array(
									array(2,1),
									array(2,-1),
									array(-2,1),
									array(-2,-1),
									array(1,2),
									array(-1,2),
									array(1,-2),
									array(-1,-2)
									);
		return($this);
	}
}

class whirligigs extends singlestep{
	function cell_action(){
		slow_move($this->waiting_time);
	}
	
}
class whirligig_2 extends whirligigs{
	//9 - 5 вертушки на 2 хода
	private $waiting_time = 2;
	function __construct(){
		return($this);
	}
}
class whirligig_3 extends whirligigs{
	//10 - 4 вертушка на 3 хода
	private $waiting_time = 3;
	function __construct(){
		return($this);
	}
}
class whirligig_4 extends whirligigs{
	//11 - 2 вертушка на 4 хода 
	private $waiting_time = 4;
	function __construct(){
		return($this);
	}
}
class whirligig_5 extends whirligigs{
	//12 - 1 вертушка на 5 ходов 
	private $waiting_time = 5;
	function __construct(){
		return($this);
	}
}
class ice extends automove_cell{
	//13 - 6 лед
	function __construct(){
		return $this;
	}
	function move_in($unit){
		parent::move_in($unit);
		$next_cell_id = $this->cell_id + ($unit->position - $unit->previous_position);
		$unit->move_to($next_cell_id,FALSE);
	}
}
class catcher extends singlestep{
	//14 - 3 капкан
	function __construct(){
		return $this;
	}
	function cell_action(){
		catch_pirate();
	}
}
class gun extends automove_cell{
	//15 - 2 пушка  
	function __construct(){
		$this->possible_move = array(array(0,-100));
		return $this;
	}
	function cell_action(){
		repeat_move();
	}
}
class fort extends singlestep{
	//16 - 2 крепость 
	function __construct(){
		return $this;
	}
	function cell_action(){
		safe_peace();
	}
}
class aborigenka extends singlestep{
	//17 - 1 аборигенка 
	function __construct(){
		return $this;
	}
	function cell_action(){
		safe_peace();
		resqure_pirate();
	}
}
class rom extends singlestep{
	//18 - 4 ром 
	private $waiting_time = 1;
	function __construct(){
		return $this;
	}
	function cell_action(){
		wait($this->waiting_time);
	}
}
class crocodille extends cells{
	//19 - 4 крокодил
	function __construct(){
		return $this;
	}
	function move_in($unit){
		parent::move_in($unit);
		$unit->move_to($unit->previous_position);
	}
}
class cannibal extends cells{
	//20 - 1 людоед 
	function __construct(){
		return $this;
	}
	function move_in($unit){
		$unit->unit_die();
	}
}
class aerostat extends cells{
	//21 - 2 воздушный шар 
	function __construct(){
		return $this;
	}
	function move_in($unit){
		parent::move_in($unit);
		$player = game::get_player($_SESSION["player_id"]);
		$player->move_finished = TRUE;
		$unit->go_to_ship();
	}
}
class airplane extends cells{
	//22 - 1 самолет  
	function __construct(){
		if($this->ship_there){
			for($i = 0; $i<169; $i++){
				$this->possible_next_cells[] = $i;
			}
		}else{
			$this->possible_next_cells = array();
			$this->possible_move=array( array(0,1), array(0,-1), array(1,-1), array(1,0),
										array(1,1), array(-1,-1), array(-1,0), array(-1,1));
		}
		return $this;
	}
	function move_in($unit){
		parent::move_in($unit);
	}
	function move_out($unit){
		$this->ship_there = FALSE;
	}
	function cell_action(){
		check_to_use();
	}
}
class storage_1 extends singlestep{
	//23 - 5 клад 1 монета 
	function __construct(){
		$this->coins_count  = 1;
		return $this;
	}
}
class storage_2 extends singlestep{
	//24 - 5 клад 2 монеты 
	function __construct(){
		$this->coins_count  = 2;
		return $this;
	}
}
class storage_3 extends singlestep{
	//25 - 3 клад 3 монеты 
	function __construct(){
		$this->coins_count  = 3;
		return $this;
	}
}
class storage_4 extends singlestep{
	//26 - 2 клад 4 монеты 
	function __construct(){
		$this->coins_count  = 4;
		return $this;
	}
}
class storage_5 extends singlestep{
	//27 - 1 клад 5 монет  
	function __construct(){
		$this->coins_count  = 2;
		return $this;
	}
}
class sea extends cells{
	//28 - 48 море 
	function __construct(){
		$this->possible_move = array(0 => array(13,14), 1=>array(13,14), 13=>array(1,14),26=>array(14), 2=>array(14), 14=>array(0,1,2,13,26), 12 => array(24,25), 11=>array(24,25), 25=>array(11,24), 10=>array(24), 24=>array(10,11,12,25,38), 38=>array(24), 156=>array(143,144), 157=>array(143,144), 158=>array(144), 143=>array(144,157), 130=>array(144), 144=>array(130,143,156,157,158),142=>array(154), 155=>array(154,167), 168=>array(154,155), 167=>array(154,155), 166=>array(154), 154=>array(142,155,166,167,168));
		return $this;
	}
	function move_in($unit){
		parent::move_in($unit);
		if(!in_array($this->cell_id,
				array(0,1,11,12,13,14,24,25,143,144,154,155,156,157,167,168))){
					$prev_cell = game::get_cell($unit->previous_position);
					if(is_a($prev_cell,"ship")){
						//Перемещение корабля за игроком
						$prev_id = $prev_cell->cell_id;
						$prev_cell->cell_id = $this->cell_id;
						$this->cell_id = $prev_id;
						$this->update_info_in_db();
						$prev_cell->update_info_in_db();
						server::add("cell",$this);
						server::add("cell",$prev_cell);
						//Убийство юнитов на которых корабль наехал
						$units = game::get_units_from_cell($this->cell_id);
						foreach($units as $k=>$u){
							if($k == $unit->id){ continue; }
							$u->unit_die();
						}
						//Перемещение юнитов на корабле за кораблем
						$units = game::get_units_from_cell($this->cell_id);
						foreach($units as $k=>$u){
							if($k == $unit->id){ continue; }
							$u->previous_position = $u->position;
							$u->position = $prev_cell->cell_id;
							$u->possible_move = $prev_cell->possible_next_cells;
							$u->save_unit_property();
							server::add("move_list",array($u->previous_position, 
																$u->position));
							loger::save(4,json_encode(array($u->id=>array($u->previous_position,
																		$u->position))));
						}
					}	
				}
	}
}
class ship extends cells{
	//29 - 4 корабль на море   
	function __construct(){
		return $this;
	}	
	function move_in($unit){
		parent::move_in($unit);
		//Тут будет обработка того когда юнит принес золото на корабль
	}
}
class closed extends cells{
	//30 - 0 не открытая клетка    
	function __construct(){
		return $this;
	}	
}
?>
