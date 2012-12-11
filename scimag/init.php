<?php


$dbHost='127.0.0.1';
$dbName='scimag';
$dbLogin='root';
$dbPassword='';
$cashing=false;


define ('SITE_ROOT',$_SERVER['DOCUMENT_ROOT']."/scimag");
$cookies = array();
set_time_limit(0);
include_once(SITE_ROOT."/include/tnv_common.php");
include_once(SITE_ROOT."/include/tnv_mysql.class.php");
include_once(SITE_ROOT."/include/tnv_files.php");
include_once(SITE_ROOT."/include/tnv_mysql.class.php");
include_once(SITE_ROOT."/include/tnv_curl.php");
include_once(SITE_ROOT."/include/tnv_torrents.php");
include(SITE_ROOT."/include/tnv_options.class.php");

include_once(SITE_ROOT."/include/tnv_forms.class.php");
include_once(SITE_ROOT."/include/tnv_sprs.class.php");
include_once(SITE_ROOT."/include/validator.class.php");
include_once(SITE_ROOT."/include/tnv_tree.class.php");



	$mysql = new tnv_mysql(array('dbHost'=>$dbHost,'dbLogin'=>$dbLogin,'dbPassword'=>$dbPassword,'dbName'=>$dbName));

	if (!$mysql->dbLink) {
		echo 'Р°';

		die;
		}
include_once(SITE_ROOT."/include/tnv_log.class.php");
$log = new tnv_log(array('dbTable'=>'tnv_log','mysql'=>$mysql));


set_time_limit(0);


?>