<?php


error_reporting('E_ALL');
 ini_set('display_errors', 1);

define(SITE_ROOT,$_SERVER['DOCUMENT_ROOT']);
$dbHost='127.0.0.1';
$dbName='scimag';
$dbLogin='root';
$dbPassword='40971355';
include('common/db_connect.php');
include('common/tnv_curl.php');
include('common/tnv_files.php');
include('common/tnv_mysql.class.php');
$dbTable="billg_sci";
	$mysql = new tnv_mysql(array('dbHost'=>$dbHost,'dbLogin'=>$dbLogin,'dbPassword'=>$dbPassword,'dbName'=>$dbName));
	if (!$mysql->dbLink) {
		echo 'Проблема подключения к БД.';
		die;
		}

$today = getdate();

$mon = $today["mon"];

if ($mon < 10) {
$mon = '0'.$mon;
}

$day = $today["mday"];
$day = $day - 1;
if ($day<10){$day = '0'.$day;}

$filedate = $today[year].'-'.$mon.'-'.$day.'.txt';
$filedate = str_replace();
$date = $today[year].'-'.$mon.'-'.$day;

echo $date;


$mysql->query("SELECT * INTO OUTFILE 'D:/!torjournals4/scihub/scihubdailyupd/".$date.".txt' FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\n' FROM scimag.scimag WHERE `TimeAdded` like '".$date."%'"); if (!$res1) echo mysq_error();
//$mysql->query("SELECT * INTO OUTFILE 'D:\\!torjournals4/scihub/".$date.".txt' FIELDS TERMINATED BY '\t' LINES TERMINATED BY '\n' FROM scimag.scimag WHERE `TimeAdded` like '2012-05%'"); if (!$res1) echo mysq_error();

?> 