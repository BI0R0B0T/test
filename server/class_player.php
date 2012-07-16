<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mikhail
 * Date: 29.06.12
 * Time: 17:20
 * To change this template use File | Settings | File Templates.
 * @version 0.3
 */
class player
{
    public $player_id;
    public $first_name;
    public $last_name;
    public $photo;
    public $photo_rec;
    public function __construct($add){
        $this->player_id = $add["player_id"];
        $this->first_name   = is_null($add["first_name"])?"no":$add["first_name"];
        $this->last_name   = is_null($add["last_name"])?"no":$add["last_name"];
        $this->photo     = is_null($add["photo"])?"no":$add["photo"];
        $this->photo_rec   = is_null($add["photo_rec"])?"no":$add["photo_rec"];
    }
    /**
    * Вырезает из GET запроса информацию о игроке
    * @return object player
    */
	public static function new_from_get(){
		$add = explode("?",$_GET["uid"]);
		$count = count($add);
		$add["player_id"] = $add[0];
		for($i = 1; $i<$count; $i++){
			$v = explode("=", $add[$i]);
			$add[$v[0]] = $v[1];
		}
		return new player($add);
	}
	/**
	* @return void
	*/
	public function add_in_session(){
		$_SESSION = array(
			"player_id" => $this->player_id,
			"first_name" => $this->first_name,
			"last_name" => $this->last_name,
			"photo" => $this->photo,
			"photo_rec" => $this->photo_rec,
			"SID" => session_id(),
			"play" => 0,
			"gameId" => null
		 );
 	}
}
