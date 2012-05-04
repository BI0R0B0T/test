<?php
class UserInfo
{
	private $user_id;
	public $first_name;		// Имя пользователя
	public $last_name;		// Фамилия пользователя
	public $photo;		
	public $photo_rec;		
	public $online;   
	public $played;   
	
	public function __construct($id, $first_name='', $last_name='', $photo='', $photo_rec='', $online=FALSE, $played = FALSE)
	{
		$this->user_id = $id;
		$this->first_name = $first_name;
		$this->last_name = $last_name;
		$this->photo = $photo;
		$this->photo_rec = $photo_rec;
		$this->online = $online;
		$this->played = $played;
	}
}
?>