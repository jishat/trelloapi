<?php
namespace App\Session;

class Session{
	public static function sessionGet($key){
		if(isset($_SESSION[$key])){
			return $_SESSION[$key];
		}else{
			return false;
		}
	}
	public static function sessionSet($key, $value){
		$_SESSION[$key] = $value;
	}

	public static function sessionDestroy(){
		session_unset();
		session_destroy();
	}
}
