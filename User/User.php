<?php 
	namespace User;
	use \Config\Database;
	use PHPMail\PHPMail;
	
	class User{
		
		private $db;
		
		public function __construct(){
			$this->db = new Database(01);
		}
		
		public function addUser($email, $pass, $phone){
			
			if($this->searchUser($email)){
				throw new UserError("Email address already exists.");
			}
			
			$stmt = $this->db->query("insert into `users` (email, password, phone) values(?,?,?)", [$email, $this->generatePassHash($pass), $phone]);
			if($stmt->affected_rows == 1){
				return true;
			}
			return false;
		}
		public function searchUser($email, $id = null){
			if($id){
				$col = "id";
				$val = $id;
			}
			else{
				$col = "email";
				$val = $email;
			}
			$stmt = $this->db->query("select * from `users` where {$col} = ?",[$val]);
			if($this->db->getRowCount()){
				return $stmt[0];
			}
			return false;
		}
		public function login($email, $pass, $remember_me = null){
			if(!$this->isUserLoggedIn()){
				$user = $this->searchUser($email);
				if($user){
					
					if(password_verify($pass, $user->password)){
						$this->createSession($user, $remember_me);
					}
					else{
						throw new UserError("Invalid email/Password");
					}
				}
				else{
					throw new UserError("Invalid email/Password");
				}
			}
			else{
				throw new UserError("Already logged in");
			}
		}
		
		public function checkSession(){
			if(!$this->isUserLoggedIn()){
				$cookie = $this->getCookie();
				
				if($cookie){
					$tokenDetails = $this->retrieveCookieToken($cookie[0]);
					if($tokenDetails){
						if(hash_equals($tokenDetails->token, $cookie[1])){
							$user = $this->searchUser(null, $tokenDetails->user_id);
							$this->createSession($user);
							return true;
						}
						else{
							$this->deleteCookieToken($cookie[0]);
							$this->removeCookie();
						}
					}
					else{
						$this->removeCookie();
					}
				}
			}
			return false;
		}
		
		public function forgotPassword($email){
			if(!$this->isUserLoggedIn()){
				
				$user = $this->searchUser($email);
				if($user){
					echo "here";
					$obj = new PHPMail;
					$token = $this->generateToken();
					$msg = "Please follow the link below to reset your password<br/><br/>".linkToPasswordResetPage.$token;
					$obj->sendMail($email, "Password Reset Request", $msg, "Reset your password");
					$stmt = $this->db->query("insert into `password_reset_requests` (email, token, expiration_date) values(?,?,?)", [$email, $token, time() + (24 * 60 * 60)]);
				}
				return "We have received your request. Follow the instructions once you receive an email";
			}
			else{
				throw new UserError("User already logged in.");
			}
		}
		
		public function logMeOut(){
			if($this->isUserLoggedIn()){
				$cookie = $this->getCookie();
				$this->deleteCookieToken($cookie[0]);
				$this->removeCookie();
				unset($_SESSION['current_user']);
				session_destroy();
				return true;
			}
			else{
				throw new UserError("No logged in User");
			}
		}
		
		public function logeMeOutOfAllDevices($id){
			if$this->logMeOut()){
				$stmt = $this->db->query("delete from user_sessions where user_id = ?",[$id]);
			}
		}
		
		public function isUserLoggedIn(){
			if(isset($_SESSION['current_user'])){
				return true;
			}
			return false;
		}
		
		public function verifyToken($token){
			$stmt = $this->db->query("select * from `password_reset_requests` where `token` = ?", [$token]);
			if($this->db->getRowCount() < 1){
				throw new UserError("Link has expired or doesn't exist.");
				return false;
			}
			else{
				if(time() < $stmt[0]->expiration_date){
					return $stmt[0]->email;
				}
				$stmt = $this->db->query("delete from `password_reset` where `token` = ?", [$token]);
				throw new UserError("The Link has expired.");
			}
		}
		
		public function resetPassword($password, $email){
			$hash = $this->generatePassHash($password);
			$stmt = $this->db->query("update `users` set password  = ? where email = ?", [$hash, $email]);
			if($stmt->affected_rows == 1){
				$stmt = $this->db->query("delete from `password_reset_requests` where email = ?",[$email]);
			}
		}
		
		//should be used with cron job
		public function deleteExpriedSessions(){
			$stmt = $this->db->query("delete from `user_sessions` where expires > ?", [time()]);
		}
		
		private function deleteCookieToken($token_id){
			$stmt = $this->db->query("delete from user_sessions where id = ?",[$token_id]);
		}
		private function createSession($user, $rememb = null){
			unset($user->password);
			
			if($rememb){
				$this->createCookie($user->id);
			}
			
			$_SESSION['current_user'] = $user;
			$this->updateLastLogin($user->email);
			
		}
		
		private function createCookie($user_id){
			
			$cookieToken = $this->addCookieToken($user_id, $this->generateToken(30));
			$cookieVal = $cookieToken[0]."#".$cookieToken[1];
		
			setcookie("loggedInUser", $cookieVal, time() + (86400 * cookieExpirationDate), "/"); // 86400 = 1 day
		}
		
		private function addCookieToken($id, $token){
			$time = time() + (86400 * cookieExpirationDate);
			$stmt = $this->db->query("insert into `user_sessions`(user_id, token, expires) values(?,?,?)",[$id, $token, $time]);
			return [$stmt->insert_id, $token];
		}
		private function getCookie(){
			if(isset($_COOKIE[cookieName])){
				return explode("#",$_COOKIE[cookieName]);
			}
			return false;
		}
		private function retrieveCookieToken($token_id){
			$stmt = $this->db->query("select * from user_sessions where id = ?", [$token_id]);
			if($this->db->getRowCount() < 1){
				return false;
			}
			return $stmt[0];
		} 
		
		private function updateLastLogin($email){
			$stmt = $this->db->query("update `users` set `last_login` = ? where `email` = ?",[time(), $email]);
		}
		
		private function generatePassHash($str){
			return password_hash($str, PASSWORD_BCRYPT, array('cost' => 10));
		}
		
		private function generateToken($length = 20)
		{
			return bin2hex(random_bytes($length));
		}
		private function removeCookie(){
			unset($_COOKIE[cookieName]);
			setcookie(cookieName, '', time() - 3600, '/');
		}
	}
