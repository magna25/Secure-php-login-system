<?php 
	use User\User;
	use User\UserError;
	
	$obj = new User;
	
	try{
		//if user is found; it returns the email address else returns false;
		$verify = $obj->verifyToken($_GET['token']);
		
		if($verify){
			//reset by calling the below method
			$obj->resetPassword("new_password", $verify);
		}
	}
	catch(UserError $u){
		
		echo $u->getMessage();
	}