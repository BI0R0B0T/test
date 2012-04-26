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
  	public static function new_cell($type, $cell_id){
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
			default: $return =  new gun();
		}
        $return->cell_id = $cell_id;
		$return->add_same_info($type);
		$return->set_possible_next_cells($cell_id);
		return $return;
	}
	protected function add_same_info($type){
		$this->rotate = (int)round(rand(0,3));
//		$this->rotate = 2;
		$this->type = $type;
	}
	private function __construct(){}
	protected function set_possible_next_cells($cell_id){
		$row = floor($this->cell_id/13);
		$column = $this->cell_id%13;
 		foreach($this->possible_move as $move){
 			$this->rotate_move($move,$this->rotate);
			if(abs($move[0]) < 100 && abs($move[1]) <100){
				$next_col = $move[0]+$column;
				$next_row = $move[1]+$row;
				if($next_col < 0 || $next_col > 12) {continue;}
				if($next_row < 0 || $next_row > 12) {continue;}
				$this->possible_next_cells[] = (int)($next_row*13+$next_col);
			}else{
				if(0 == $move[0]){
					$this->possible_next_cells[0] = $move[1]>0?(int)(12*13+$column):(int)$column;
				}else{
					$this->possible_next_cells[0] = $move[0]>0?(int)($row*13+12):(int)($row*13);
				}
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

?>
