<?php

class image_storage {
	var $Version = "0.3";
	var $dbTable="tnv_gallery3_images";
	var $Sql='';
	var $dbLink= '';
	var $Error = '';
	var $image_info = array();
	var $config = array();
	var $image_path = '/images/tnv_gallery3';
	var $site_path = '';
	var $small_width = 250;
	var $middle_width = 0;
	var $obj_id = '';
	var $resize=true;


	function getError(){
		$str = '<div style=" border : thin red;backgroud : #EEEEEE">SQL: '.$this->Sql.'<br>sqlError: '.$this->Error.' </div>';
		return $str;
		}

	function showError(){
		echo $this->getError();
		}

	function image_storage ($dbLink,$site_path){
		$this->dbLink = $dbLink;
		$this->site_path = $site_path;
		}

	function dbQuery($sql){
		$this->Sql = $sql;
		//echo "$sql<br>";
		 if (!$res = mysql_query($sql,$this->dbLink)) { $this->Error=mysql_error(); return false;}
		 return $res;
		}

	function dbNumRows($res){
		return mysql_num_rows($res);
		}

	function getNewOrd($obj_id){
 		$sql = "select max(ord_id) as ord_id from $this->dbTable where img_gid=$obj_id";
 		if (!$res=$this->dbQuery($sql)) { return false; }
 		$row = $this->dbfetchArray($res);
 		if ($row['ord_id']=='') return 1;
 		else return $row['ord_id']+1;
		}

    function save_image ($image_info){
    	$this->image_info=$image_info;
    	$ord = $this->getNewOrd($this->obj_id);  if (!$ord) { $this->showError();}
        $query="insert into $this->dbTable (img_gid,img_localname,img_realname,img_smallname";
        if (isset($image_info['middlename'])) $query.=",img_middlename";
		$query.=",ord_id";
        if (isset($image_info['image_name'])) $query.=",img_name";
        if (isset($image_info['image_desc'])) $query.=",img_desc";
        $query.=") values ('".$this->obj_id."','".$this->image_info['localname']."','".$this->image_info['realname']."','".$this->image_info['smallname']."'";
		if (isset($image_info['image_name'])) $query.=",'".addslashes($image_info['middlename'])."'";
		$query.=",$ord";
        if (isset($image_info['image_name'])) $query.=",'".addslashes($image_info['image_name'])."'";
        if (isset($image_info['image_desc'])) $query.=",'".addslashes($image_info['image_desc'])."'";
        $query.=")";
        $result=$this->dbQuery($query);
        //echo "+++++ $query"; echo $this->Error;
        if ((mysql_affected_rows($this->dbLink)>0) and (mysql_error()=='')) {
        	$this->image_info['id']=mysql_insert_id($this->dbLink);
        	return $this->image_info['id'];
        	}
        	else
        	{
        	$this->Error=mysql_error();
        	return false;
        	}
    	}

    function delete_image($id){
    	global $HTTP_SERVER_VARS;
    	$sql = "select * from $this->dbTable where img_id = $id";
        $res = $this->dbQuery($sql);
        if (mysql_num_rows($res)==0) { $this->Error=mysql_error(); return false;}
        else {
        	$row = mysql_fetch_array($res);
        	if (file_exists($HTTP_SERVER_VARS['DOCUMENT_ROOT'].$row['img_smallname'])) @unlink($HTTP_SERVER_VARS['DOCUMENT_ROOT'].$row['img_smallname']);
        	//echo "unlink('".$HTTP_SERVER_VARS['DOCUMENT_ROOT'].$row['img_localname']."')";
        	@unlink($HTTP_SERVER_VARS['DOCUMENT_ROOT'].$row['img_localname']);
        	if (file_exists($HTTP_SERVER_VARS['DOCUMENT_ROOT'].$row['img_middlename'])) @unlink($HTTP_SERVER_VARS['DOCUMENT_ROOT'].$row['img_middlename']);
        	$sql = "delete from $this->dbTable where img_id=$id";
        	$res = $this->dbQuery($sql);
        	if (!$res) {$this->Error.=mysql_error(); return false;}
        	}
        return true;
    	}

    function dbFetchArray($res){
    	return mysql_fetch_assoc($res);
    	}

    function delete_images ($obj_id){
    	$sql = "select * from $this->dbTable where img_gid = $obj_id";
    	$res=$this->dbQuery($sql);
    	if (!$res) return false;
    	while ($row=$this->dbFetchArray($res)){
    		$this->delete_image($row['img_id']);
    		}
    	}

    function set_main_image ($img_id){
    	$sql = "select img_gid from $this->dbTable where img_id = $img_id";
    	$res=$this->dbQuery($sql); $row = $this->dbFetchArray($res);
    	$obj_id = $row['img_gid'];
    	$sql = "update $this->dbTable set img_main=0 where img_gid=".$obj_id;
    	$res=$this->dbQuery($sql);
    	$sql = "update $this->dbTable set img_main=1 where img_id=".$img_id;
    	$res=$this->dbQuery($sql);
    	return true;
    	}

    function get_images_count($obj_id){
    	$sql="select count(*) cn from $this->dbTable where img_gid = $obj_id";
    	$res=$this->dbQuery($sql);
    	if (!$res) {$this->Error = mysql_error(); return false;}
    	$row = $this->dbFetchArray($res);
    	return $row['cn'];
    	}

    function get_image($id){
    	$sql="select * from $this->dbTable where img_id=$id";   // echo "$sql";
    	if (!$res=$this->dbquery($sql)) {$this->Error=mysql_error(); return false;}
    	$image = $this->dbFetchArray($res);
    	return $image;
    	}

    function get_images($obj_id,$start=0,$count=10000000000){
    	$sql="select * from $this->dbTable where img_gid = $obj_id order by ord_id limit $start,$count";
    	if(!$res=$this->dbQuery($sql)) return false;
    	//echo "----";
    	$i=0;
    	if (!$res) {$this->Error = mysql_error(); return false;}
    	$images = array();
    	if ($this->dbNumRows($res)>0){
	    	while($row=$this->dbFetchArray($res)){
	    		$images[$i]['img_smallname']=$row['img_smallname'];
	    		$images[$i]['img_middlename']=$row['img_middlename'];
	    		$images[$i]['img_localname']=$row['img_localname'];
	    		$images[$i]['img_id']=$row['img_id'];
	    		$images[$i]['img_main'] = $row['img_main'];
	    		$images[$i]['img_name'] = stripslashes($row['img_name']);
	    		$images[$i]['img_desc'] = stripslashes($row['img_desc']);
	    		$images[$i]['ord_id'] = stripslashes($row['ord_id']);
	    		$images[$i]['view_count'] = $row['view_count'];
	     		$i++;
	    		}
    	}
    	return $images;
    	}

    function upload_image($image) {
    	include_once('imageresize.php');
    	$rnd=rand(1,99999999);
        $img_localname="$this->image_path/$rnd-".$image['name'];
        $img_smallname="$this->image_path/small/small_$rnd-".$image['name'];// echo $image['tmp_name']." $this->site_path
		$img_middlename="$this->image_path/middle/middle_$rnd-".$image['name'];
        if (move_uploaded_file($image['tmp_name'],$this->site_path."/$img_localname")) {
        //	echo "$this->site_path/$img_localname $this->site_path/$img_smallname";
        	if ($this->resize) {
        		if (!image_resize("$this->site_path/$img_localname", "$this->site_path/$img_smallname", $this->small_width)) echo "Resize error";
        		if ($this->middle_width>0)
        			if (!image_resize("$this->site_path/$img_localname", "$this->site_path/$img_middlename", $this->middle_width)) echo "middle Resize error";
					//print "$this->small_width $this->middle_width";	
/*         	 		else
         			{
         				$this->Error .= "Не могу перенести файл из временного каталога\n";
         				return false;
         			}*/
         } else $img_smallname=$img_localname;
        }
        $image_info['localname']=$img_localname;
        $image_info['realname']=$image['name'];
        $image_info['smallname']=$img_smallname;
        $image_info['middlename']=$img_middlename;
        $image_info['image_name']=$image['image_name'];
        $image_info['image_desc']=$image['image_desc'];
        if (!$id=$this->save_image($image_info)) { return false;}
        return $id;
    	}

	function getMainImage($obj_id){
		$sql="select * from $this->dbTable where img_gid = $obj_id and img_main=1";
		$res=$this->dbQuery($sql);
		if ($this->dbNumRows($res)>0) return $this->dbFetchArray($res);
		else return false;
		}

	function update_image($rec){
		$sql="update $this->dbTable set img_name='".addslashes($rec['image_name'])."',img_desc='".addslashes($rec['image_desc'])."' where img_id=".$rec['img_id'];
		$res=$this->dbQuery($sql);
		if (!$res) return false;
			else return true;
		}

	function moveUp($img_id){
		$sql="select * from $this->dbTable where img_id=$img_id";  //echo "$sql<br>";
		$res=$this->dbQuery($sql);	$row=$this->dbFetchArray($res); $ord_id=$row['ord_id']; $img_gid=$row['img_gid']; //echo $ord_id."<br>";
		$sql="select * from $this->dbTable where ord_id<=$ord_id and img_gid=$img_gid order by ord_id desc limit 0,2";
		$res=$this->dbQuery($sql);
		$row=$this->dbFetchArray($res); $ord1=$row['ord_id']; $gal1=$row['img_id'];  //echo "ord1=$ord1 gal1=$gal1<br>";
		$row=$this->dbFetchArray($res); $ord2=$row['ord_id']; $gal2=$row['img_id'];  //echo "ord2=$ord2 gal2=$gal2<br>";
		$sql="update $this->dbTable  set ord_id=$ord2 where img_id=$gal1"; //echo "$sql<br>";
		$res=$this->dbQuery($sql);
		$sql="update $this->dbTable  set ord_id=$ord1 where img_id=$gal2"; //echo "$sql<br>";
		$res=$this->dbQuery($sql);
		return true;
		}

	function moveDown($img_id){
		$sql="select * from $this->dbTable where img_id=$img_id"; //echo "$sql<br>";
		$res=$this->dbQuery($sql);	$row=$this->dbFetchArray($res); $ord_id=$row['ord_id']; $img_gid=$row['img_gid']; //echo $ord_id."<br>";
		$sql="select * from $this->dbTable where ord_id>=$ord_id  and img_gid=$img_gid order by ord_id limit 0,2";
		$res=$this->dbQuery($sql);
		$row=$this->dbFetchArray($res); $ord1=$row['ord_id']; $gal1=$row['img_id'];   //echo "ord1=$ord1 gal1=$gal1<br>";
		$row=$this->dbFetchArray($res); $ord2=$row['ord_id']; $gal2=$row['img_id'];   //echo "ord2=$ord2 gal2=$gal2<br>";
		$sql="update $this->dbTable  set ord_id=$ord2 where img_id=$gal1"; //echo "$sql<br>";
		$res=$this->dbQuery($sql);
		$sql="update $this->dbTable  set ord_id=$ord1 where img_id=$gal2"; //echo "$sql<br>";
		$res=$this->dbQuery($sql);
		return true;
		}

	function moveEnd($img_id){
	    $cur=$this->get_image($img_id);
	    $sql="select max(ord_id) as ord_id from $this->dbTable where img_gid=".$cur['img_gid'];
	    $res=$this->dbQuery($sql);  $row=$this->dbFetchArray($res);
        $sql = "update $this->dbTable set ord_id=".($row['ord_id']+1)." where img_id=".$cur['img_id'];
        $res=$this->dbQuery($sql);
		return true;
		}

	function moveBegin($img_id){
	    $cur=$this->get_image($img_id);
	    $sql="select min(ord_id) as ord_id from $this->dbTable where img_gid=".$cur['img_gid'];
	    $res=$this->dbQuery($sql);  $row=$this->dbFetchArray($res);
        $sql = "update $this->dbTable set ord_id=".($row['ord_id']-1)." where img_id=".$cur['img_id'];
        $res=$this->dbQuery($sql);
		return true;
		}

	function viewAdd($img_id){
		$sql="update $this->dbTable set view_count=view_count+1 where img_id=$img_id";
		$this->dbQuery($sql);
		return true;
		}
	}
?>