<?php 
    //Author: Henok Hailemariam @2017
	
	use User\User;
	use User\UserError;
	
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}
	
	//auto loader for classes
	spl_autoload_register(function ($className){
		$className = str_replace('\\', '/', $className);
		$file = $_SERVER['DOCUMENT_ROOT']."/{$className}.php";
		require_once $file;
	});
	
	const cookieName = "loggedInUser";
	const cookieExpirationDate = 100; //in days
	const linkToPasswordResetPage = "http://localhost/resetPassword?token=";
	
	//check session for auto login
	$obj = new User;
	$obj->checkSession();
	
	function isUserloggedIn(){
		if(isset($_SESSION['current_user'])){
			return true;
		}
		return false;
	}
	
	