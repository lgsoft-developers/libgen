<?php

/**
 * @author 
 * @copyright 2009
 */

class tnv_mysql {
	var $version=0;
	var $dbHost='localhost';
	var $dbLogin='root';
	var $dbPassword='';
	var $dbName='';
	var $dbLink='';
	var $dbSql='';
	var $dbError='';
	
	function store_sql ($sql) {
		$this->dbSql=$sql;
	}
	
	function store_error($sql,$error) {
		$this->dbError=$error;
	}
	
	function tnv_mysql ($pars){
		if ($pars['dbHost']!='') $this->dbHost=$pars['dbHost'];
		if ($pars['dbLogin']!='') $this->dbLogin=$pars['dbLogin'];
		if ($pars['dbPassword']!='') $this->dbPassword=$pars['dbPassword'];
		if ($pars['dbName']!='') $this->dbName=$pars['dbName'];
		$this->dbLink=mysql_connect($this->dbHost, $this->dbLogin, $this->dbPassword) or
        die("Could not connect: " . mysql_error());
		mysql_select_db($this->dbName,$this->dbLink);
		
		mysql_query ("set character_set_client='utf-8'");
		mysql_query ("set character_set_results='utf-8'");
		mysql_query ("set collation_connection='utf-8_general_ci'");
			
	}
	
	function query($sql) {
		$this->store_sql($sql);
		
		if (!$res=mysql_query($sql)){
			$this->store_error($sql, mysql_error()); echo $sql." ".mysql_error();
			return false;
		}
		return $res;
	}
	
	function fetch_assoc($res) {
		return mysql_fetch_assoc($res);
	}
	
	function num_rows($res) {
		return mysql_num_rows($res);
	}
	
	function last_inserted_id($link='') {
		if ($link=='') $link=$this->dbLink;
		return mysql_insert_id($link);
	}
	
	function getError(){
		return $this->dbSql." ".$this->dbError;
	}
	
	function getFieldValue($sql,$field){
		$res = $this->Query($sql);
		if (!$res) return false;
		$row = $this->fetch_assoc($res);
		return $row[$field];
	}
}

?>