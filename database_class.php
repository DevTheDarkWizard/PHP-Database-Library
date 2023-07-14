<?php
	include("database_config.php");

	class Database {
		//Variaveis da classe
		private $host;
		private $user;
		private $pass;
		private $database;
		private $port;
		private $con;
		private $auto_commit;
		private $error;

		//Construtor da classe
		function __construct($auto_commit = true) {
			//Pega as variáveis do arquivo de configuração
			global $db_user, $db_pass, $db_host, $db_database, $db_port;
			
			$this->host =  $db_host;
			$this->user = $db_user;
			$this->pass =  $db_pass;
			$this->database =  $db_database;
			$this->port =  $db_port;
			
			$this->auto_commit = $auto_commit;
			$this->error = array();
		}
		
		//Conecta no banco de dados
		function Connect() {
			$this->con = mysqli_init();
			$this->con->options(MYSQLI_CLIENT_FOUND_ROWS,true);
			$this->con->real_connect(
				$this->host, 
				$this->user, 
				$this->pass, 
				$this->database, 
				$this->port) or 
				die("Fail to connect error number: ".mysqli_connect_errno());

			$this->con->set_charset("utf8");
			$this->con->autocommit($this->auto_commit);
		}
		
		function ChangeHost($host) {
			$this->host = $host;
		}
		
		function ChangeUser($user) {
			$this->user = $user;
		}
		
		function ChangePassword($pass) {
			$this->pass = $pass;
		}
		
		function ChangeDatabase($database) {
			$this->database = $database;
		}
		
		function ChangePort($port) {
			$this->port = $port;
		}

		function GetError() {
			return $this->error;
		}

		function Commit() {
			if($this->con && !count($this->error)) {
				$this->con->commit();
			}
		}
		
		function Rollback() {
			if(!$this->con) return;
			$this->con->rollback();
		}
		
		function Close() {
			if(!$this->con) return;

			$this->con->close();
			$this->con = null;
		}
		
		function GetLastInsertID() {
			if(!$this->con) return null;
			return $this->con->lastInsertId();
		}

		function GetInsertID() {
			if(!$this->con) return null;
			return $this->con->insert_id;
		}
		
		function Execute() {
			if($this->con) {
				//Pega os parâmetros inseridos
				$num_args = func_num_args();
				if($num_args > 0) {
					$result = null;
					$query_result = null;
					$args = func_get_args();
					
					if(count($args) == 1 && count($args[0]) > 1) $args = $args[0];

					$query = $args[0];
					array_shift($args);
					
					//Prepara a query
					if($stmt = $this->con->prepare($query)) {
						if(count($args) > 0){
							$type_args = "";
							foreach($args as $arg){
								$type = gettype($arg)[0];
								if($type == "N") $type = "s";
								$type_args .= strval($type);
							}
							$stmt->bind_param($type_args, ...$args);
						}
						
						$stmt->execute();
						$query_result = $stmt->get_result();

						$this->error = $stmt->error_list;
						
						$affected_rows = $stmt->affected_rows;

						preg_match_all('!\d+!', $this->con->info, $m);
						$matched_rows = $m[0];

						if(count($matched_rows)) $matched_rows = intval($matched_rows[0]);
						else $matched_rows = 0;

						//Pega o resultado do banco
						if($matched_rows || ($query_result === true || $query_result === false) && ($affected_rows>0 || $affected_rows==-1)) {
							//Resultado de alteração
							$result = $affected_rows == -1 ? false : true;
						} else {
							//Resultado de dados
							$result = array();
							if($query_result !== true && $query_result !== false) {
								while ($myrow = $query_result->fetch_assoc()) {
									$result[]=$myrow;
								}
							}
						}
						$stmt->close();
					}

					//Gera o resultado final
					$final_result = null;

					if($result === true || $result === false) $final_result = $result;
					else if(sizeof($result) > 0) {
						foreach($result as $r) {
							$final_result[] = $r;
						}
					}
					else $final_result = null;
					
					if(count($this->error)) $final_result = false;
					
					return $final_result;
				} else {
					return null;
				}
			}
		}
	}
?>