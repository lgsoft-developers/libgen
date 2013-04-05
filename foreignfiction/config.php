<?php 
$dbHost='127.0.0.1';
$dbName='fiction';
$dbLogin='root';
$dbPassword='';


$mysql = mysql_connect($dbHost, $dbLogin, $dbPassword);
if (!$mysql) die("Could not connect to the database: ".mysql_error());


	mysql_query("SET session character_set_server = 'UTF8'");
	mysql_query("SET session character_set_connection = 'UTF8'");
	mysql_query("SET session character_set_client = 'UTF8'");
	mysql_query("SET session character_set_results = 'UTF8'");
	mysql_select_db('fiction', $mysql);

?>