<?php
class unit{
	public $id;
	public $die;
	public $have_coins;
	public $position;
	public $cell_part;
	public $can_move;
	public $possible_move;
	public $color;
	private $master;
	private $waitng_time;
	private $previous_position;
	function __construct($id, $master,$position, $die, $have_coins, $cell_part,
						$waitng_time, $previous_position,$possible_move=array()){
		$this->master = $master;
		$this->position = $position;
		$this->die = (bool)$die;
		$this->have_coins = (bool)$have_coins;
		$this->cell_part = $cell_part;
		$this->can_move = $waitng_time?FALSE:TRUE;
		$this->possible_move = $possible_move;
		$this->waitng_time = $waitng_time;
		$this->previous_position = $previous_position; 
		$this->id = $id;
		$this->color = 0xFF0000;
	}
	static function get_units_from_db(){
		$db = game_db::db_conn();
		$sql = "SELECT id, master_id, have_coin, waiting_time, die, cell_position_id, previous_position, cell_part FROM units ";
		$res = $db->query($sql);
		game_db::check_error($sql);
		$units = array();
		while($unit = $res->fetchArray(SQLITE3_ASSOC)){
			$units[$unit["id"]] = new unit( $unit["id"],$unit["master_id"],$unit["cell_position_id"],
											$unit["die"],$unit["have_coin"],$unit["cell_part"],
											$unit["waiting_time"],$unit["previous_position"]);
		}
		return $units;
	}
	function save_unit_property(){
		$db = game_db::db_conn();
		$sql = "UPDATE units SET have_coin = ".($this->have_coins?1:0).", waiting_time = ".$this->waitng_time;
		$sql.=", die = ".($this->die?1:0).", cell_position_id = ".$this->position.", cell_part = ".$this->cell_part;
		$sql.=" WHERE id = ".$this->id;
		game_db::check_error($sql);
		$db->query($sql);		
	}
	static function born_unit_on_ship($number){
		static $ship = array(6, 162, 78, 90);
		$cell = $ship[$number];
		$db = game_db::db_conn();
		$sql = "INSERT INTO units (id, master_id, have_coin, waiting_time, die, cell_position_id, ";
		$sql.= "previous_position, cell_part) VALUES(null, ".$_SESSION["player_id"].", 0, 0, 0, ";
		$sql.= "$cell, $cell, 0)";
		$db->query($sql);
		game_db::check_error($sql);
	}
	/**
	* Получение юнита из БД
	* @param string $unit_id
	* @return object unit
	* @version 0.2
	*/
	static function get_unit_from_db($unit_id){
		$db = game_db::db_conn();
		$unit_id = (int)substr($unit_id,5);
		$sql = "SELECT id, master_id, have_coin, waiting_time, die, cell_position_id, previous_position, cell_part FROM units WHERE id = ".$unit_id;
		$res = $db->query($sql);
		game_db::check_error($sql);
		$units = array();
		while($unit = $res->fetchArray(SQLITE3_ASSOC)){
			$units[$unit["id"]] = new unit( $unit["id"],$unit["master_id"],$unit["cell_position_id"],
											$unit["die"],$unit["have_coin"],$unit["cell_part"],
											$unit["waiting_time"],$unit["previous_position"]);
		}
		return $units[$unit_id];
	}
	/**
	* Перемещение юнита по полю
	* @param int $cell_id
	* @param boolean $need_return
	* @return mixed array|void
	* @version 0.2
	*/
	public function move_to($cell_id, $need_return = FALSE){
		$db = game_db::db_conn();
		loger::save(3,json_encode(array("start_move")), $_SESSION["player_id"]);
		//Проверяем возможен ли такой ход
		$prev_cell = cells::get_cell_from_db($db,$this->position);
		if(!in_array($cell_id,$prev_cell->possible_next_cells)){
			echo json_encode(array("status"=>"FAIL", 
									"info"=>"imposible move from ".$this->position." to ".$cell_id ));
			return;
		}
		if(!$need_return){$this->previous_position = $this->position;}
		$this->position = $cell_id;
		//получаем информацию о клетке на которую идет юнит
		$cell = cells::get_cell_from_db($db,$cell_id);
		$return = array("status" => "OK", "you_move" => 0);
		if(30 == $cell->type){
			$cell = cells::open_cell($db,$cell_id);
			$return["map"][] = $cell;
		}
		//обрабатываем автомувы
		if($cell->auto_move){
			if(1==count($cell->possible_next_cells)){
				$prev_return = $this->move_to($cell->possible_next_cells[0], TRUE);
				if(isset($prev_return["map"]) && !empty($prev_return["map"])){
					foreach($prev_return["map"] as $v){
						$return["map"][] = $v;
					}
				}
				$return["move_list"] = $prev_return["move_list"];
			}
			$return["you_move"] = 1;
		}
		$this->possible_move = $cell->possible_next_cells;
		$this->save_unit_property();
		$return["units"][] = $this;
		$return["move_list"][] = array($this->previous_position, $this->position);
		if($need_return){
			return $return;
		}else{
			echo json_encode($return);	
		}
	}
	/**
	* Проверка на то может ли данный юнит сейчас двигаться
	* @return boolean
	* @version 0.2
	*/
	public function checkPossibleMove(){
		return TRUE;
		$db = game_db::db_conn();
		//Проверяем чей сейчас ход.
		$previous = loger::who_was_last();
		if($previous == $_SESSION["player_id"]){
			echo json_encode(array("status"=>"FAIL", 
									"info"=>"This isn't your turn. Reason 1"));
			return FALSE;
		}
		if($this->who_will_next($previous) != $_SESSION["player_id"]){
			echo json_encode(array("status"=>"FAIL", 
									"info"=>"This isn't your turn. Reason 2 (id = ".$_SESSION["player_id"]
											." want ".$this->who_will_next($previous).")"));
			return FALSE;
		}
		return TRUE;
	}
	/**
	* Возвращает id игрока кто должен ходить следующим
	* @param int $previous id предыдущего игрока
	* @return int
	* @version 0.1
	*/
	private function who_will_next($previous){
		$db = game_db::db_conn();
		$sql ="SELECT players.player_id FROM players WHERE players.played = 1";
		$res = $db->query($sql);
		game_db::check_error($sql);
		$users = array();
		$next = FALSE;
		while($user = $res->fetchArray(SQLITE3_ASSOC)){
			if(TRUE == $next){ return $user['player_id'];}
			$users[] = $user['player_id'];
			if($user['player_id'] == $previous){ $next = TRUE; }
		}
		
		return $users[0];
	}
}
?>