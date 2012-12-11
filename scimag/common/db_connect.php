<?php
$link=mysql_connect($dbHost, $dbLogin, $dbPassword) or
        die("Could not connect: " . mysql_error());
mysql_select_db($dbName,$link);
mysql_query ("set character_set_client='utf-8'");
mysql_query ("set character_set_results='utf-8'");
mysql_query ("set collation_connection='utf-8_general_ci'");
?>