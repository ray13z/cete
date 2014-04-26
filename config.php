<?php

	$db_host 		= "localhost";
	$db_username 	= "rayner";
	$db_password 	= "marines";
	$db_dbname  	= "cete";
	
	$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
	
	try {
		$db = new PDO("mysql:host={$db_host};dbname={$db_dbname};charset=utf8", $db_username, $db_password, $options);
		// echo "Connected to <h3>$db_dbname@$db_host: $db_username</h3>"."<br>";
	} catch (PDOException $ex) {
		die("Failed to connect to the database: " . $ex->getMessage());
	}
	
	$db -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db -> setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	
	header('Content-Type: text/html; charset=utf-8');
//	session_start();

    if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 
?>