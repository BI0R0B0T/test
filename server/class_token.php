<?php
/**
* Токен для связи с соцсетями
*/
class token{
  	  public $player_id;
	  private $token = null;
	  private $expires_in = null;
	  public function __construct(){
		//заглушка
	  }
	/**
	* Проверяем не испортился ли билетик
	* @return boolean
	*/
	public function check(){
		return ($this->expires_in > time());	 
	}
	public function new_token($player_id, $token, $expers_in){
		$this->player_id = $player_id;
		$this->token = $token;
		$this->expires_in = $expers_in;
		$this->save();		
		$this->save_in_session();	  
	}
	  
	public function get_from_db($player_id){
		$db = game_stat::get_db();
		$stmt = $db->prepare("SELECT token, expires_in FROM tokens WHERE id =:id");
		$stmt->bindValue(':id', $player_id, SQLITE3_INTEGER);
		$result = $stmt->execute();
		$result = $result->fetchArray();
		if(empty($result)){
			return FALSE;
		}else{
			$return = new token();
			$return->player_id = $player_id;
			$return->token = $result["token"];
			$return->expires_in = $result["expires_in"];
			return $return;
		}
	}
	public function get_token(){
		return $this->token;
	}
	public function get_experies_in(){
		return $this->expires_in;
	}
	public function save(){
		//Проверка на то есть ли токен для данного пользователя
		if($prev = $this->get_from_db($this->player_id)){
			//check need to update
			if($this->expires_in > $prev->get_experies_in()){
				return;
			}
			//update info
			$sql = "UPDATE tokens SET token = '".$this->token."', expires_in = ".$this->expires_in." ";
			$sql.= "WHERE tokens.id = ".$this->player_id;
		}else{
			//add new
			$sql = "INSERT INTO tokens(id, token, expires_in) VALUES(";
			$sql.= $this->player_id.", '".$this->token."', ".$this->expires_in.")";
		}
		$db = game_stat::get_db();
		$resault = $db->query($sql);
		game_stat::check_error();
	}
	private function save_in_session(){
		$_SESSION["expires_in"] = $this->expires_in;
		$_SESSION["tiket"] = $this->tiket_generator();
	}
	private function tiket_generator(){
		return md5($this->player_id.$this->token);
	}
  }
?>
