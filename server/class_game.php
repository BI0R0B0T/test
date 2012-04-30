<?php
class game{
	private static $game_id = NULL;
	private static $map = NULL;
	private static $player_id = NULL;
	private static function __construct(){
		self::$map = map::map_generate();
		self::$game_id = self::$map::$map_id;
	}
	public static function __destruct(){
		
	}
	private static function __clone(){
		
	}
	public static function start_game(){
		new game();
		return self::$game_id;
	}
	public static function get_game($game_id){
		self::$game_id = $game_id;
		self::$map = map::get_map(self::$game_id);
		return self::$game_id;
	}
	public static function stop_game($game_id){
		unlink($game_id);
	}
	private static function connvert_2_JSON(){
		
	}
}
?>