<?php 
	use User\User;
	use User\UserError;
	
	$obj = new User;
	try{
		
		//********Adding a user***********
		//Only an email, password and phone required but you can expand this to your needs;
		$obj->addUser("test@gmail.com", "testpassword", "7201234567");
		
		//********Logging In**************
		//acccepts email, password and optional rememberme number(anything > 0);*/
		$obj->login("test@gmail.com", "testpassword", 1);
		//valid also: no remember me
		$obj->login("test@gmail.com", "testpassword");
		
		
		//***logging out**
		$obj->logMeOut();
		//logout from all devices
		$obj->logMeOutFromAllDevices();
		
		
		//****Forgot Password****
		//this method will send a reset link to the registered email address 
		$obj->forgotPassword('test@gmail.com');
		
		/******To check if User is logged in
		returns @boolean*/
		$obj->isUserLoggedIn();
		//or call global function
		isUserLoggedIn();
		
		//********to protect a page simply include the above function at the top of your page
		if(!isUserLoggedIn){
			//redirect to login page
		}
		
		//***********The Database Class***********//
		//This login system includes a database class which is reusable;
		//first add your database by editing loadDatabase function in database.php
		//then querying is simple; example below;
		//include the class at top
		use Config\Database;
		
		//pass the key of the database you are using..this is defined in Database.php loadDatabase()
		$db = new Database(01);
		//it also accepts a second parameter if you want to use PDO; the default api is mysqli
		$db = new Database(01,"pdo");
		
		//the query method accepts the sql followed by array of parameters separated by comma
		//it returns array of objects for select statments and mysql object for the rest;
		$stmt = $db->query("select * from your_table where email = ? and name = ?", ["test@mail.com", "your_name"]);
		
		//loop through the results for select statments
		foreach($stmt as $row){
			echo $row->first_name;
		}
		
		//some of the methods
		
		//changes the default api
		$db->setApi("pdo");
		
		//changes database in use;
		$db->setDbKey(2);
		
		//gets row count
		$db->getRowCount();
	}
	catch (UserError $u){
		echo $u->getMessage();
	}
