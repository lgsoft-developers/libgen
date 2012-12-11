

<?php

class tnv_options {
	var $version = '0.1';
	var $dbTableData='';
	var $dbLink = '';

 	function tnv_options ( $dbLink,$dbTable) {
		$this->dbLink = $dbLink;
		$this->dbTableData = $dbTable;
		}

	function show_error(){
		$str = '<div style=" border : thin red;backgroud : #EEEEEE">SQL: '.$this->Sql.'<br>sqlError: '.$this->sqlError.'<br>Error: '.$this->Error.' </div>';
		echo $str;
		}

   function dbQuery($sql){
    	$this->sql = $sql;
    	//echo "$sql<br>";
        $res=mysql_query($sql,$this->dbLink);
        if (!$res) {
                $this->Error = mysql_error();
            	}
        return $res;
    }

    function dbFetchArray($res){
                $row=mysql_fetch_array($res);
                return $row;
     }

    function InsertedId(){
     	return mysql_insert_id($this->dbLink);
     	}

    function dbNumRows($res){
     	return mysql_num_rows($res);
     	}

	function get_variable($id,$web=0){
		$sql = "select * from $this->dbTableData where var_id=$id";
		$res = $this->dbQuery($sql);
		$row = $this->dbFetchArray($res);
		if ($row['var_type']==1) return $row['var_value_int'];
		if ($row['var_type']==2) {
			$str=stripslashes($row['var_value_text']);
			if ($web==1){
				$str=str_replace('<','&lt;',$str);
				$str=str_replace('>','&gt;',$str);
				}
			return $str;
			}
		}

	function save_variable($id,$value,$web=0){
		$sql = "select * from $this->dbTableData where var_id=$id";
		if (!$res = $this->dbQuery($sql)) return false;
		$vrow= $this->dbFetchArray($res);
		if ($vrow['var_type']==1) $sql = "update $this->dbTableData set var_value_int='$value' where var_id=$id";
		if ($vrow['var_type']==2) {
			if ($web==1){
				$value=str_replace('&lt;','<',$value);
				$value=str_replace('&gt;','>',$value);
				}
			$sql = "update $this->dbTableData set var_value_text='".addslashes($value)."' where var_id=$id";
			}
		if (!$res = $this->dbQuery($sql)) return false;
		return true;
		}

    }
?>
