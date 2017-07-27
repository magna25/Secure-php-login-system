<?php 
	//Author: Henok Hailemariam @2017
	namespace Config;
	use PDO;
	use mysqli;
	
	class Database{
		
		private $db_api = "mysqli";
		private $call_type;
		private $db;
		private $rowCount = 0;
		
		public function __construct($db_code,$db_api = null){
			if($db_api){
				$this->db_api = $db_api;
			}
			$this->db = $this->loadDatabase($db_code);
		}
		
		public function setApi($api){
			$this->db_api = $api;
		}
		
		public function setDbKey($key){
			$this->db = $this->loadDatabase($key);
		}
		
		public function query($sql, $params = null){
			
			$this->call_type = substr(strtolower(trim($sql)), 0, 3) == "sel" ? "S" : "U";
			
			if($this->db_api == "mysqli"){
				
				$stmt = $this->db->prepare($sql);
				echo $this->db->error;
				if(isset($params)){
					$params = array_merge(array(str_repeat('s', count($params))), $params);
					call_user_func_array(array($stmt, "bind_param"), $this->refValues($params));
				}
				
				$stmt->execute();
				
				if($this->call_type == "S"){
					$res = $stmt->get_result();
					$this->rowCount = $res->num_rows;
					$rows = [];
					while($row = $res->fetch_object()){
						$rows[] = $row;
					}
					return $rows;
				}
				
				return $stmt;
			}
			else if($this->db_api == "pdo"){
				$this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
				$stmt = $this->db->prepare($sql);
				$stmt->execute($params);
				
				$this->rowCount = $stmt->rowCount();
				
				if($this->call_type == "U"){
					return $stmt->rowCount();
				}
				return $stmt->fetchAll(PDO::FETCH_OBJ);
			}
			else{
				throw new DatabaseError($this->db_api." database API is not supported");
			}
			return false;
		}
		
		//list all your databases here
		private function loadDatabase($key){
			$databases = [
				01 => ["localhost","root", "", "test_db"],
				02 => ["host","user", "password", "database_name"],
				03 => ["host","user", "password", "database_name"]
			];
			if(array_key_exists($key,$databases)){
				
				if($this->db_api == "mysqli"){
					
					return new mysqli($databases[$key][0], $databases[$key][1], $databases[$key][2], $databases[$key][3]);
				
				}
				else if($this->db_api == "pdo"){
					
					return new PDO("mysql:host={$databases[$key][0]};dbname={$databases[$key][3]}", $databases[$key][1],$databases[$key][2]);
				
				}
				
			}
			throw new DatabaseError("Database key not found. Add the database key.");
		}
		public function getRowCount(){
			return $this->rowCount;
		}
		private function refValues($arr){ 
			if(!is_array($arr)){
				throw new DatabaseError("Function refValues expects array");
			}
			$refs = []; 
			foreach($arr as $key => $value){ 
				$refs[$key] = &$arr[$key]; 
			}
			return $refs;
		} 
	}