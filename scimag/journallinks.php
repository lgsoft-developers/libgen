<?php 


echo "<html><body>";
include ('init.php');
	mysql_query("SET session character_set_server = 'UTF8'");
	mysql_query("SET session character_set_connection = 'UTF8'");
	mysql_query("SET session character_set_client = 'UTF8'");
	mysql_query("SET session character_set_results = 'UTF8'");

$siteid = $_GET['siteid'];


$journaldois = "SELECT id, doi FROM scimag where scimag.siteid = '$siteid'";
//echo $journaldois;
$journaldois = mysql_query($journaldois);

while ($journaldoisrows = mysql_fetch_assoc($journaldois)){
$journaldoisdoi = stripslashes($journaldoisrows['doi']);

if($journaldoisrows['id'] < 5741524){$urlpath = '../scimag1';}
elseif ($journaldoisrows['id'] > 5741523 and $journaldoisrows['id'] < 10222441) {$urlpath = '../scimag2';}
elseif ($journaldoisrows['id'] > 10222440 and $journaldoisrows['id'] < 15157523) {$urlpath = '../scimag3';}
else {$urlpath = '../scimag4';}



$doimagazine=substr($journaldoisdoi, 8);
$doipublisher=substr($journaldoisdoi, 0, 8);



$doilinks = '<a href="'.$urlpath.'/'.$doipublisher.''.rawurlencode(rawurlencode($doimagazine)).'.pdf">'.$journaldoisdoi.'</a><br>';

echo $doilinks;
}

echo "</body></html>";

?>