<?php
	require_once("example_usage.php");

	echo "INSERT<br>";
	var_dump(InsertData(array("username" => "account_test3","password" => "test_password3", "name" => "Character Test 4")));

	echo "SELECT ALL<br>";
	var_dump(SelectAll());

	echo "UPDATE ACCOUNT PASSWORD<br>";
	var_dump(UpdateAccountPassword(array("id" => 1, "password" => "new password")));

	echo "SELECT ACCOUNT BY ID<br>";
	var_dump(SelectAccountById(1));

	echo "DELETE ACCOUNT<br>";
	var_dump(DeleteAccount(1));

	echo "SELECT ALL<br>";
	var_dump(SelectAll());
?>