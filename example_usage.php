<?php
	require_once('database_class.php');

	function InsertData($data) {
		//Resultado
		$result = array();
		$accountId = null;
		$characterId = null;

		//Verificação simples nos dados recebidos
		if($data) {
			//Verificação dos campos necessários
			$require = array("username", "password", "name");
			foreach($require as $key) {
				if(!array_key_exists($key, $data)) {
					$result["status"] = "Fail";
					$result["message"][] = "Key require: $key";
				}
			}

			//Verificação dos valores
			foreach($data as $key => $value) {
				if($value === null || !is_array($value) && strlen(strval($value)) == 0) {
					$result["status"] = "Fail";
					$result["message"][] = "Check value of the key: $key";
				}
			}
		} else {
			$result["status"] = "Fail";
			$result["message"][] = "Data is empty!";
		}

		if(!count($result)) {
			//Cria uma nova instância da classe do banco com a transação desativada
            $database = new Database(false); 

			//Conectar no banco
            $database->Connect();

			//Query para a inserção da conta
            $query = "insert into accounts (username, password) value (?,?);";

			//Executa a query e pega o resultado
            $res = $database->Execute($query, $data["username"], $data["password"]);

			//Verifica o retorno do banco
            if($res) {
				//Pega o último valor inserido
                $accountId = $database->GetInsertID();

				//Query para a inserção do personagem
                $query = "insert into characters (id_account, name) values (?, ?);";

                $res = $database->Execute($query, $accountId, $data["name"]);

                if($res) {
					$characterId = $database->GetInsertID();

					//Gera o resultado e confirma a transação
                    $result["status"] = "OK";
                    $database->Commit();
                }
                else{
					//Gera o resultado e cancela a transação
                    $database->Rollback();
                    $result["status"] = "Fail";
					//$error = $database->GetError();
                }
            }else{
                $database->Rollback();
                $result["status"] = "Fail";
				//$error = $database->GetError();
            }

			//Fecha a conexão do banco
            $database->Close();
        }
        
        return [$result, $accountId, $characterId];
	}

	function SelectAll() {
		//Cria uma nova instância da classe do banco com a transação ativada por padrão
        $database = new Database(); 

		//Conectar no banco
        $database->Connect();

		//Query de seleção
		$query = "select a.*, c.name from accounts as a
		inner join characters as c on (a.id = c.id_account)";

		//Executa a query e pega o resultado
        $data = $database->Execute($query);

		//Fecha a conexão do banco
        $database->Close();

        return $data;
	}

	function SelectAccountById($accountId) {
		//Resultado
		$data = null;
		$result = array();

		//Verificação do valor inserido
		if($accountId === null || !is_int($accountId)) {
			$result["status"] = "Fail";
			$result["message"][] = "Check the account id!";
		}

		if(!count($result)) {
			//Cria uma nova instância da classe do banco com a transação ativada por padrão
			$database = new Database(); 

			//Conectar no banco
			$database->Connect();

			//Query de seleção
			$query = "select a.*, c.name from accounts as a
			inner join characters as c on (a.id = c.id_account)
			where a.id = ?";

			//Executa a query e pega o resultado
			$data = $database->Execute($query, $accountId);

			if($data) {
				$result["status"] = "OK";
			} else {
				$result["status"] = "Fail";
				$result["message"][] = "Something goes wrong!";
				//$error = $database->GetError();
			}
			//Fecha a conexão do banco
			$database->Close();
		}

        return [$result, $data];
	}

	function UpdateAccountPassword($data) {
		//Resultado
		$result = array();
		
		//Verificação simples nos dados recebidos
		if($data) {
			//Verificação dos campos necessários
			$require = array("id","password");
			foreach($require as $key) {
				if(!array_key_exists($key, $data)) {
					$result["status"] = "Fail";
					$result["message"][] = "Key require: $key";
				}
			}

			//Verificação dos valores
			foreach($data as $key => $value) {
				if($value === null || !is_array($value) && strlen(strval($value)) == 0) {
					$result["status"] = "Fail";
					$result["message"][] = "Check value of the key: $key";
				}
			}

			if(array_key_exists("id", $data) && !is_int($data["id"])) {
				$result["status"] = "Fail";
				$result["message"][] = "Check the account id!";
			}
		} else {
			$result["status"] = "Fail";
			$result["message"][] = "Data is empty!";
		}

		
        if(!count($result)) {
			//Cria uma nova instância da classe do banco com a transação desativada
            $database = new Database(false);

			//Conectar no banco
            $database->Connect();

			//Query de alteração de dados
			$query = "update accounts set password = ? where id = ?;";

			//Executa a query e pega o resultado
            $res = $database->Execute($query, $data["password"], $data["id"]);

            if($res){
				//Gera o resultado e confirma a transação
                $database->Commit();
                $result["status"] = "OK";
            }else{
				//Gera o resultado e cancela a transação
                $database->Rollback();
                $result["status"] = "Fail";
				//$error = $database->GetError();
            }

			//Fecha a conexão do banco
            $database->Close();
        }
        
        return $result;
	}

	function DeleteAccount($accountId) {
		//Resultado
		$result = array();

       	//Verificação do valor inserido
		if($accountId === null || !is_int($accountId)) {
			$result["status"] = "Fail";
			$result["message"][] = "Check the account id!";
		}

        if(!count($result)) {
			//Cria uma nova instância da classe do banco com a transação desativada
            $database = new Database(false); 

			//Conectar no banco
            $database->Connect();

			//Query de exclusão dos personagens
			$query = "delete from characters where id_account = ?;";

			//Executa a query e pega o resultado
            $res = $database->Execute($query, $accountId);

            if($res){

				//Query de exclusão da conta
				$query = "delete from accounts where id = ?;";

				$res = $database->Execute($query, $accountId);

				if($res){
					//Gera o resultado e confirma a transação
					$database->Commit();
					$result["status"] = "OK";
				}else{
					//Gera o resultado e cancela a transação
					$database->Rollback();
					$result["status"] = "Fail";
					//$error = $database->GetError();
				}
            }else{
				//Gera o resultado e cancela a transação
                $database->Rollback();
                $result["status"] = "Fail";
				//$error = $database->GetError();
            }

			//Fecha a conexão do banco
            $database->Close();
        }
        
        return $result;
	}
?>