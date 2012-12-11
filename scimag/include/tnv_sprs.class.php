<?php

class tnv_sprs {
	var $version = '0.1';
	var $dbTableData='tnv_razm_spr';
	var $script_path = '';
	var $site_path = '';
    var $Sql = '';
	var $Error = '';
	var $sqlError = '';
	var $dbLink = '';
	var $parent_url = '';
	var $mode='admin';
	var	$unique_id='dswe4';

	function showError(){
		$str = '<div style=" border : thin red;backgroud : #EEEEEE">SQL: '.$this->sql.'<br>sqlError: '.$this->sqlError.'<br>Error: '.$this->Error.' </div>';
		echo $str;
		}

	function getError(){
		$str = '<div style=" border : thin red;backgroud : #EEEEEE">SQL: '.$this->sql.'<br>sqlError: '.$this->sqlError.'<br>Error: '.$this->Error.' </div>';
		return $str;
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


	function tnv_sprs ( $dbLink,$site_path,$ScriptPath ) {
		$this->dbLink = $dbLink;
		$this->site_path = $site_path;
		$this->script_path = $ScriptPath;
		}

  	function getRecord($id){
  		$sql = "select * from $this->dbTableData where id=$id";
 		if (!$res = $this->dbQuery($sql)) return false;
 		$row = $this->dbFetchArray($res);
 		$row = $this->desafe($row);
 		return $row;
  		}
	function showJavaScripts(){
		$str="<script language=JavaScript>
		function checkForm(){
			if (blockForm.name.value=='') { alert('Значение не может быть пустым'); return false;}
			blockForm.submit();
			}
		function closeForm(spr_id){
			location='".$this->parent_url."?a=list&spr_id='+spr_id;
			}

		function DeleteBlock(){
			blockForm.a.value='delete';
			blockForm.submit();
			}
		</script>";
		echo $str;
		}

 	function showSprForm($action,$row){
 		if ($action=='add') {
 			$row['name']='';
 			if (!isset($row['spr_id'])) $row['spr_id']=0;
 			$row['gid']=$row['spr_id'];
 			if (!$gr = $this->getRecord($row['gid'])) return false;
 			}

 		if ($action=='edit'){
 			if (!$row = $this->getRecord($row['id'])) return false;
 			if (!$gr = $this->getRecord($row['gid'])) return false; //print_r($gr);
 			}

		$nameStyle="style='font-weight : bold;'";
 		$valueStyle='';
 		$elStyle="style='border : 1px solid #DADADA'";
 		echo "<br><br><form name=blockForm action='$this->parent_url' method=POST enctype='multipart/form-data'>\n";
 		echo "<table border=0 class='text' width=400 align=center>\n";
 		echo "<tr><th colspan=2 bgcolor=#DADADA>Фома добавления позиции в справочник</td></tr>\n";
		echo "<tr><td $nameStyle>Название</td><td $valueStyle><input type=edit name='name' value='".$row['name']."' size=70 $elStyle></td></tr>\n";
		if ($gr['text_enable']==1) echo "<tr><td $nameStyle>Значение</td><td $valueStyle><input type=edit name='value_text' value='".$row['value_text']."' size=70 $elStyle></td></tr>\n";
		echo "<tr><td colspan=2 align=right>";
        if ($action=='edit') {
        	echo "<input type=hidden name=a value='update'>";
        	echo "<input type=hidden name=id value='".$row['id']."'>";
       		echo "<input type=button value='Закрыть' onclick='javascript:closeForm(".$row['gid'].")' $elStyle>&nbsp;";
        	echo "<input type=button value='Удалить' onclick='javascript:DeleteBlock()' $elStyle>&nbsp;";
        	}
        if ($action=='add') {
        	echo "<input type=hidden name=a value='save'>";
        	}
       	echo "<input type=button value='Сохранить' onclick='javascript:checkForm()' $elStyle>";
 		echo "</td></tr\n>";
 		echo "</table>\n";
 		echo "<input type=hidden name=spr_id value=".$row['gid'].">\n";
 		echo "</form>\n";
 		}

		function safe($row){
            if (isset($row['name'])) $row['name']=addslashes($row['name']);
            if (isset($row['value_text'])) $row['value_text']=addslashes($row['value_text']);
			return $row;
			}

		function desafe($row){
            if (isset($row['name'])) $row['name']=stripslashes($row['name']);
            if (isset($row['value_text'])) $row['value_text']=stripslashes($row['value_text']);
			return $row;
			}

	function getMaxOrdId($spr_id){
		$sql = "select max(ord) as ord from $this->dbTableData where gid=$spr_id";
		if (!$res=$this->dbQuery($sql)) return false;
		$row = $this->dbFetchArray($res);
		return $row['ord'];
		}

	function getNextValue($spr_id){
		$sql = "select max(value) as value from $this->dbTableData where gid=$spr_id";
		if (!$res=$this->dbQuery($sql)) return false;
		$row = $this->dbFetchArray($res);
				//echo "++++".$row['value'];
		return $row['value'];
		}

    function processAction($action,$row){

		$row=$this->safe($row);

		if ($action=='show' && $this->mode=='admin'){
			$sql="update $this->dbTableData set status=1 where id=".$row['id'];
			if (!$res=$this->dbQuery($sql)) {
				$this->sqlError=mysql_error();
				return false;
				}
			}

		if ($action=='hide' && $this->mode=='admin'){
			$sql="update $this->dbTableData set status=0 where id=".$row['id'];
			if (!$res=$this->dbQuery($sql)) {
				$this->sqlError=mysql_error();
				return false;
				}
			}

   		if ($action=='save'){
   			$ord_id=$this->getMaxOrdId($row['spr_id'])+1;
   			$value=$this->getNextValue($row['spr_id'])+1;
			if (!isset($row['value_text'])) $row['value_text']='';
   			$sql="insert into $this->dbTableData (gid,name,value,value_text,ord,status) values (".$row['spr_id'].",'".$row['name']."','$value','".$row['value_text']."',$ord_id,1)";
			if (!$res=$this->dbQuery($sql)) {
				$this->sqlError=mysql_error();
				return false;
				}
			$id=mysql_insert_id($this->dbLink);
            return $id;
   		}

   		if ($action=='update'){
   			if (isset($row['show_on_main'])) $show_on_main=1; else $show_on_main=0;
   			if (!isset($row['value_text'])) $row['value_text']='';
			$sql="update $this->dbTableData set name='".$row['name']."',value_text='".$row['value_text']."' where id=".$row['id'];
			if (!$res=$this->dbQuery($sql)) {
				$this->sqlError=mysql_error();
				return false;
				}
   		}
   		if ($action=='delete'){
   			$sql="delete from $this->dbTableData where id=".$row['id'];
   			if(!$res=$this->dbQuery($sql)) return false;
   			}

    return true;
   	}


 	function getSelect ($name,$spr_id,$id,$def = '&nbsp;',$add=''){
  		$out='';
  		$out.="<select name='$name' $add>\n";
  		$sql = "select * from $this->dbTableData where gid=$spr_id and status=1 order by ord,name";
  		if (!$res=$this->dbQuery($sql)) return false;
  		$out.="<option value=0 >$def</option>\n";
  		while ($row = $this->dbFetchArray($res)) {
  			$out.="<option value=".$row['value']." "; if ($row['value']==$id) $out.=" selected "; $out.=" >".$row['name']."</option>\n";
  			}
  		$out.= "</select>";
  		return $out;
  		}

 	function getSelectOrdered ($name,$spr_id,$id,$def = '&nbsp;',$selectClass='',$orderType='name'){
  		$out='';
  		$out.="<select name='$name' $selectClass>\n";
  		$sql = "select * from $this->dbTableData where gid=$spr_id and status=1 order by $orderType";
  		if (!$res=$this->dbQuery($sql)) return false;
  		$out.="<option value=0 >$def</option>\n";
  		while ($row = $this->dbFetchArray($res)) {
  			$out.="<option value=".$row['value']." "; if ($row['value']==$id) $out.=" selected "; $out.=" >".$row['name']."</option>\n";
  			}
  		$out.= "</select>";
  		return $out;
  		}


	function getSprValue($spr_id,$id){
		$sql = "select * from $this->dbTableData where gid=$spr_id and value='$id'";
		$res = $this->dbQuery($sql);
		$row = $this->dbFetchArray($res);
		return $row['name'];
		}


	function showSprList($spr_id){
    	echo "<table width=600 align=center>";
		echo "<tr><td align= center style='border : 1px solid; background-color: #DADADA;'>";
		echo "<form name=sprname action='$this->parent_url' method=GET>";
		echo "<b>Справочник</b>&nbsp;".$this->getSelect('spr_id',0,$spr_id);
		echo "<input type=submit value='Показать'>";
		echo "</form>";
		echo "</td></tr><tr><td>";
		if ($spr_id!=0){
			$sql = "select * from $this->dbTableData where gid=$spr_id order by name";
			if (!$res=$this->dbQuery($sql)){ $this->Error .='ошибка в запросе выбора данных по справочнику '; return false;		}
			if ($spr_id>0) echo "<a href='$this->parent_url?a=add&spr_id=$spr_id'>Добавить</a>";
			echo "<table width=100% cellspacing=0 border=1>";
			echo "<tr bgcolor=#DADADA><th>Название</th><th>Управление</th></tr>";
			while ($row  = $this->dbFetchArray($res)) {
	            echo "<tr><td>".stripslashes($row['name'])."</td><td width=100><a href='$this->parent_url?spr_id=$spr_id&id=".$row['id']."&a=edit'>Редактрировать</a></td></tr>";
				}
			echo "</table>";
		}
		echo "</td></tr>";
	 	echo "</table>";
        return true;
		}


	function showSprListFlat($spr_id=0,$id=0){
		echo "<table align=center class='text'>";
		echo "<tr><td>Наименование справочника</td></tr>";
        $sql="select * from $this->dbTableData where gid=$id and status=1";
        if (!$res=$this->dbQuery($sql)){ $this->Error .='ошибка в запросе выбора списка справочников '; return false;  }
		while ($row  = $this->dbFetchArray($res)) {
				if ($row['value']==$spr_id) echo "<tr><td><a href='$this->parent_url?id=".$row['value']."&a=edit'><b>".stripslashes($row['name'])."</b></a></td></tr>";
	            else echo "<tr><td><a href='$this->parent_url?spr_id=".$row['value']."&a=list'>".stripslashes($row['name'])."</a></td></tr>";
				}
		echo "</table>";
		return true;
		}



	function showSprContent($spr_id){
    	echo "<table width=600 align=center>";
		echo "<tr><td>";
		if ($spr_id!=0){
			$sql = "select * from $this->dbTableData where gid=$spr_id order by name";
			if (!$res=$this->dbQuery($sql)){ $this->Error .='ошибка в запросе выбора данных по справочнику '; return false;		}
			if ($spr_id>0) echo "<a href='$this->parent_url?a=add&spr_id=$spr_id'>Добавить</a>";
			echo "<table width=100% cellspacing=0 border=1>";
			echo "<tr bgcolor=#DADADA><th>Название</th><th>Управление</th></tr>";
			while ($row  = $this->dbFetchArray($res)) {
	            echo "<tr><td>".stripslashes($row['name'])."</td><td width=100><a href='$this->parent_url?spr_id=$spr_id&id=".$row['id']."&a=edit'>Редактрировать</a></td></tr>";
				}
			echo "</table>";
		}
		echo "</td></tr>";
	 	echo "</table>";
	 	return true;
		}

}
?>