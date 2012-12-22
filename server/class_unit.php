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
	public $draggable;
	private $master;
	private $waitng_time;
	public $previous_position;
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
		$master = game::get_player($this->master);
		$this->color = $master->color;
		$this->draggable = ($_SESSION["player_id"] == $this->master?TRUE:FALSE);
	}
	static function get_units_from_db(){
		try{
			$sql =  "SELECT id, master_id, have_coin, waiting_time, die, cell_position_id, ".
					"previous_position, cell_part FROM units ";
			$sth = game_db::db_conn()
					->query($sql);
			$sth->setFetchMode(PDO::FETCH_ASSOC);
			$units = array();
			while($unit = $sth->fetch()){
				$units[$unit["id"]] = new unit( $unit["id"],$unit["master_id"],
												$unit["cell_position_id"],$unit["die"],
												$unit["have_coin"],$unit["cell_part"],
												$unit["waiting_time"],$unit["previous_position"]
												);
			}
			return $units;
		}catch(PDOException $e){
			server::return_fail($e);
		}
	}
	/**
	* save unit property in game_db
	*/
	public function save_unit_property(){
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
	* @return void
	* @version 0.3
	*/
	public function move_to($cell_id, $need_return = TRUE){
        $prev_cell = game::get_cell($this->position);
		//Взаимодействие с клеткой с которой уходит юнит
		$prev_cell->move_out($this);
		$this->previous_position = $this->position;
		$this->position = $cell_id;
		server::add("move_list",array($this->previous_position,$this->position));
		loger::save(4,json_encode(array($this->id=>array($this->previous_position, 
														$this->position))));
		//получаем информацию о клетке на которую идет юнит
		$cell = game::get_cell($cell_id);
		if(30 == $cell->type){
			$cell = game::open_cell($cell_id);
			server::add("cell",$cell);
		}
		//взаимодействие с клеткой на которую пришел юнит
		$cell->move_in($this);
		$this->possible_move = $cell->possible_next_cells;
		$this->save_unit_property();
		if($need_return){
			server::add("units", game::get_units());
			$player = game::get_player($_SESSION["player_id"]);
			if($player->move_finished){
				server::add("you_move", 0);
				loger::save(5,"move finished");
			}else{
				server::add("you_move", 1);
			}
			
			server::output();
		}else{
			return;
		}
	}
	public function get_previous_position(){
		return $this->previous_position;
	}
	/**
	* Смерть юнита. Он мертв пока не произошло воскрешение в шатре амазонки
	*
	*/
	public function unit_die(){
		loger::save(6,$this->id);
		$this->die = TRUE;
		$this->save_unit_property();
	}
	/**
	* Убийство юнита. Юнит моментально воскрешается на корабле. Без монет и всего что с ним было.
	*
	*/
	public function unit_kill(){
		
	}
	/**
	* Воскрешение юнита в шатре амазонки. Только в том случае, если у игрока меньше 3ех юнитов.
	*/
	public function unit_resurect(){
		
	}
	/**
	* перемещение юнита на корабль со всем добром.
	*/
	public function go_to_ship(){
		$ship = game::get_ship($this->master);
		$this->move_to($ship->cell_id);
	}
}
?>