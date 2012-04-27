<?php
abstract class cells{
/**
* Паттерн фабрика по созданию новой клетки
* @author M.Dolgov <dolgov@bk.ru>
**/
	public $cell_id;
    public $type;
	public $rotate;
	public $can_stay_here = TRUE;
	public $auto_move = FALSE;
	public $coins_count = 0;
	public $ship_there = FALSE;
	protected $possible_move = array();//массив возможных перемещений, каждое перемещение это координаты (x,y)
	public $possible_next_cells = array();
	/**
	* @desc на основании типа вызывает генерацию нового объекта данного типа
	* @var int $type
	* @var int $cell_id
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
			$return->set_possible_next_cells($cell_id);
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
	public function save_cell_in_db($db){
		$sql = "INSERT INTO map( cell_id, type, rotate, can_stay_here, open, coins_count,"; 
		$sql .= "ship_there) VALUES (".$this->cell_to_str().")";
		$db->query($sql);
	}
	public static function get_cell_from_db($db,$id){
		$sql = "SELECT  map.cell_id, map.type, map.rotate, map.can_stay_here, map.open, ";
		$sql .= "map.coins_count, map.ship_there FROM map WHERE map.cell_id = ".$id;
		$cell = $db->query($sql);
		$res = $cell->fetchArray(SQLITE3_ASSOC);
		if(1 == $res['open']){
			$new_cell = self::new_cell($res['type'],$res['cell_id'],FALSE);
			$new_cell->rotate = $res['rotate'];
			$new_cell->can_stay_here = $res["can_stay_here"]==1?TRUE:FALSE;
			$new_cell->coins_count = $res['coins_count'];
		}else{
			$new_cell = self::new_cell(30,$res['cell_id'],FALSE);
		}
		return $new_cell;
	}
	public static function open_cell($db,$id){
		self::change_cell($db,$id,"open",1);
		return self::get_cell_from_db($db,$id);
	}
	public static function change_cell($db,$id,$property,$new_value){
		$property_list = array("type", "open", "coins_count");
		if(in_array($property, $property_list)){
			if($property == "open" || "coins_count"){
				$sql = "UPDATE map SET $property = $new_value WHERE map.cell_id = $id";
			}else{
				
			}
			$db->query($sql);
		}
		
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
				$next_col = $move[0]+$column;
				$next_row = $move[1]+$row;
				if($next_col < 0 || $next_col > 12) {continue;}
				if($next_row < 0 || $next_row > 12) {continue;}
				$this->possible_next_cells[] = (int)($next_row*13+$next_col);
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
		if(0 == $x || 0 == $y){
			if(0 == $x){
				$coordinate = $left?array($y,$x):array(-$y,$x);
			}else{
				$coordinate = $left?array($y, -$x):array($y,$x);
			}
		}else{
			$c = $x*$y;
			//определяем четверть
			if($c > 0){
				//1 or 3
				$coordinate = $left?array(-$y,$x):array($y,-$x);
			}else{
				//2 or 4
				$coordinate = $left?array($y,-$x):array(-$y,$x);
			}
		}
		return $coordinate;
	}
}

class automove_cell extends cells{
	public $can_stay_here = FALSE;
	public $auto_move = TRUE;
	
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
	function cell_action(){
		repeat_move();
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
	function cell_action(){
		abort_move();
	}
}
class cannibal extends cells{
	//20 - 1 людоед 
	function __construct(){
		return $this;
	}
	function cell_action(){
		kill_pirate();
	}
}
class aerostat extends automove_cell{
	//21 - 2 воздушный шар 
	function __construct(){
		return $this;
	}
	function cell_action(){
		move_to_sheep();
	}
}
class airplane extends cells{
	//22 - 1 самолет  
	function __construct(){
		for($i = 0; $i<169; $i++){
			$this->possible_next_cells[] = $i;
		}
		return $this;
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
}
class ship extends cells{
	//29 - 4 корабль на море   
	function __construct(){
		return $this;
	}	
}
class closed extends cells{
	//30 - 0 не открытая клетка    
	function __construct(){
		return $this;
	}	
}
?>
