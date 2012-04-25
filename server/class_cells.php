<?php
/**
* Паттерн фабрика по созданию новой клетки
* @author M.Dolgov <dolgov@bk.ru>
**/
abstract class cells{
	private $cell_id;
    private $type;
	private $rotate;
	private $can_stay_here = TRUE;
	private $auto_move = FALSE;
	private $coins_count = 0;
	private $ship_there = FALSE;
	protected $possible_move = array(); //массив возможных перемещений, каждое перемещение это координаты (x,y)
	private $possible_next_cells = array();
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
			case 0: $return =  new empty_cell();
			default: $return =  new move_up();
		}
        $return->cell_id = $cell_id;
		$return->add_same_info($type);
		$return->set_possible_next_cells($cell_id);
		return $return;
	}
	protected function add_same_info($type){
		$this->rotate = round(rand(0,4));
		$this->type = $type;
	}
	private function __construct(){}
	protected function set_possible_next_cells($cell_id){
//		var_dump($this);
//		echo "<br>";
//		exit();
		$row = floor($this->cell_id/13);
		$column = $this->cell_id%13;
 		foreach($this->possible_move as $move){
			$next_col = $move[0]+$column;
			$next_row = $move[1]+$row;
			if($next_col < 0 || $next_col > 12) {continue;}
			if($next_row < 0 || $next_row > 12)    {continue;}
			$this->possible_next_cells[] = (int)($next_row*13+$next_col);
		}
		unset($this->possible_move);
	}
	/**
	* Изменяет значение возможных движений в зависимости от угла поворота клетки. Повороты против часовой стрелки
	**/
	private function rotate_move($move, $rotation){
		switch ($rotation){
			case 0: ;
			case 1: return array($move)
		}
	}
	private function rotate_on_pi($coordinate){
		$x = $coordinate[0];
		$y = $coordinate[1];
	}
}

class empty_cell extends cells{
	function __construct(){
		$this->possible_move = array(
										array(0,1),
										array(0,-1),
										array(1,-1),
										array(1,0),
										array(1,1),
										array(-1,-1),
										array(-1,0),
										array(-1,1)
									);
//		$this->set_possible_next_cells($cell_id);
    	return($this);
	}
	private function __clone(){}
}
class move_up extends cells{
	function __construct(){
		$this->can_stay_here = FALSE;
		$this->auto_move = TRUE;
		$this->possible_move = array(array(0,-1));
		return($this);
	}
}
?>
