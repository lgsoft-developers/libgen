<?php

class tnv_tree {

var $dbTableData='';
var $dbLink='';
var $Sql='';
var $sqlError='';
var $Error='';
var $parent_url='';
var $formNum=2;
var $cat_cols=2;

function tnv_tree($pars){
    if (isset($pars['dbTableData'])) $this->dbTableData=$pars['dbTableData'];
    $this->parent_url=$pars['parent_url'];
    $this->dbLink = $pars['dbLink'];

	}

function get_list($pid=0){
	$sql = "select * from $this->dbTableData where pid=$pid order by name asc";
	if (!$res=$this->dbQuery($sql)) return false;
	$items = array ();
	while ($row = $this->desafe($this->dbFetchArray($res))){
		$items[]=$row;
		}
    return $items;
	}

function get_path($pars){
	$path='';
	$pid=$pars['pid'];
	if (isset($pars['class']))  $class=$pars['class']; else $class='text2';
	if (isset($pars['pid_param'])) $pid_param=$pars['pid_param']; else $pid_param='pid';
	while ($pid!=0){
	$sql="select * from $this->dbTableData where id=$pid";
	if (!$res=$this->dbQuery($sql)) return false;
	$row=$this->dbFetchArray($res);
	$path="<a href='$this->parent_url?".$pid_param."=".$row['id']."' class=$class>".$row['name']."</a>/".$path;
	$pid=$row['pid'];
	}
	return $path="/<a href='$this->parent_url?".$pid_param."=0' class=$class>".$pars['root']."</a>/".$path;
	}


function getRecord($id){
	$sql="select * from $this->dbTableData where id=$id";
	if (!$res=$this->dbQuery($sql)) return false;
	return $this->desafe($this->dbFetchArray($res));
	}


function process_action ($action,$pars){
	$pars = $this->safe($pars);
	if ($action=='save'){
		$sql = "insert into $this->dbTableData (pid,name,status) values (".$pars['pid'].",'".$pars['name']."',0)";
		if (!$res=$this->dbQuery($sql)) return false;
		return true;
		}

	if ($action=='update'){
		$sql = "update $this->dbTableData  set name='".$pars['name']."' where id=".$pars['id'];
		if (!$res=$this->dbQuery($sql)) return false;
		return true;
		}

	if ($action=='delete'){
		$ids = array();
        $rec=$this->getrecord($pars['id']);
        if ($rec['pid']==0) {
        	$sql = "select * from $this->dbTableData where pid=".$pars['id'];
        	$res=$this->dbQuery($sql); if (!$res) return false;
        	while($row=$this->dbFetchArray($res)){
        		$ids[]=$row['id'];
        		}
        	$sql = "delete from $this->dbTableData where pid=".$pars['id'];
        	$res=$this->dbQuery($sql);
        	$sql = "delete from $this->dbTableData where id=".$pars['id'];
        	$res=$this->dbQuery($sql);
        	}
        	else
        	{
        		$ids[]=$pars['id'];
        		$sql = "delete from $this->dbTableData where id=".$pars['id'];
        		$res=$this->dbQuery($sql);  if (!$res) return false;
       		}
       	return $ids;
		}


	}

	function safe($row){
		if (isset($row['name'])) $row['name']=addslashes($row['name']);
		return $row;
		}

	function desafe($row){
		if (isset($row['name'])) $row['name']=stripslashes($row['name']);
		return $row;
		}

	function show_javascripts($func_name){

		if ($func_name=='select'){
			$out='';
            $out.= "
			<script >
			function selectCategory(id){";
			$sql="select * from $this->dbTableData where pid=0";
			$res=$this->dbQuery($sql); //echo mysql_error();
			while ($row =$this->dbFetchArray($res)){
			$out.= "document.getElementById('id".$row['id']."').style.display='none';";
			}
			$out.="
 			if (id!=0) {document.getElementById('id'+id).style.display='inline';}
			}
			</script>
			";
			echo $out;
			}
		}

	function show_level1($pars){
		$out='';
        $sql="select * from $this->dbTableData where pid=0";
		$res=$this->dbQuery($sql); echo mysql_error();
		$out.= '<select name='.$pars['fe_name_l1'].' onchange="selectCategory(forms[0].'.$pars['fe_name_l1'].'.value)">';
		$out.= '<option value=0>..Выберите из списка</option>';
		while ($row =$this->dbFetchArray($res)){
			$out.= '<option value='.$row['id'].'>'.$row['name'].'</option>';
			}
		$out.= '</select>';
		echo $out;
  	}

	function show_level2($pars){
		$out='';
		$sql="select * from $this->dbTableData where pid=0";
		$res=mysql_query($sql); echo mysql_error();
		while ($row = mysql_fetch_assoc($res)){
		$out.= "<span style='display : none' id=id".$row['id'].">";
		$sql="select * from $this->dbTableData where pid=".$row['id'];
		$res2=$this->dbQuery($sql); echo mysql_error();
		$out.= '<select name='.$pars['fe_name_l2'].'>';
		while ($row2 = $this->dbFetchArray($res2)){
			$out.= '<option value='.$row2['id'].'>'.$row2['name'].'</option>';
			}
		$out.=  '</select></span>'."\n";
		}
		echo $out;
	}


function get_select($pars){
		if (isset($pars['id'])) $id=$pars['id']; else $id=0;
		$out='';
        $sql="select * from $this->dbTableData where pid=".$pars['pid']." order by name";
		$res=$this->dbQuery($sql); echo mysql_error();
		$out.= '<select name='.$pars['fe_name'].' ';
		if (isset($pars['onchange_function'])) $out.='onchange="'.$pars['onchange_function'].'(forms['.$this->formNum.'].'.$pars['fe_name'].'.value)"';
		$out.='>';
		$out.= '<option value=0>..Выберите из списка</option>';
		while ($row =$this->dbFetchArray($res)){
			$out.= '<option value='.$row['id'].' '; if($id==$row['id']) $out.=' selected ';	$out.=' >'.$row['name'].'</option>';
			}
		$out.= '</select>';
		return $out;
	}

# mySQL wrapper
    function dbQuery($sql){
    	$this->Sql = $sql;
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

	function show_error(){
		$str = '<div style=" border : thin red;backgroud : #EEEEEE">SQL: '.$this->Sql.'<br>sqlError: '.$this->sqlError.'<br>Error: '.$this->Error.' </div>';
		echo $str;
		}

	function show_categories($id=0){
		$sql="select * from $this->dbTableData where pid=$id order by name";
		if (!$res=$this->dbQuery($sql)) {
			$this->show_error();
			return false;
			}
		while ($row = $this->dbFetchArray($res)){
			if ($row['id']==$id) echo '<a href="'.$this->parent_url.'?category='.$row['id'].'" class=text2s>'.$row['name'].'</a>';
			else echo '<a href="'.$this->parent_url.'?category='.$row['id'].'" class=text2>'.$row['name'].'</a>';
			echo '</br>';
			}
		}


   function show_categories_layer($pars){
   		$pid=$pars['pid'];
   		$type=$pars['pid_param'];
        $sql = "select * from $this->dbTableData where pid=".$pid." order by name";
		$res=$this->dbQuery($sql); if (!$res) return false;
		$count = $this->dbNumRows($res);
		$rows = ceil($count/$this->cat_cols);
		$items = array();
		while ($row=$this->dbFetchArray($res))
			$items[]='<table><tr><td><span class="txt3">'.$row['cn'].'</span></td><td><a href="'.$this->parent_url.'?'.$type.'='.$row['id'].'&page=1" class="txt" >'.$row['name'].'</a></td></tr></table>';

		    echo "<table border=0 width=100% class=text2>";
		        for ($i=0;$i<ceil($count/$this->cat_cols);$i++){
		        	echo "<tr>\n";
		        	for ($j=0;$j<$this->cat_cols;$j++){
		        	//echo $j*$rows+$i."<br>";
		        		if (isset($items[$j*$rows+$i])) echo "<td width=".ceil(100/$this->cat_cols)."% valign=top>".$items[$j*$rows+$i]."</td>"; else echo "<td>&nbsp;</td>";
		        		}
		        	echo "</tr>\n";
		        	}
		    echo "</table>";

   		}

}


?>