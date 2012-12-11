<?php 


    // Установка куки для запоминания выбора языка
    if(isset($_COOKIE['lang'])) { 
       $lang = $_COOKIE['lang'];
       $lang_file = 'lang_'.$lang.'.php';
       if(!file_exists($lang_file)) { $lang_file = 'lang_en.php'; }
    } else {
         $lang = 'en';
         $lang_file = 'lang_en.php';
       }
     // -- Конец установки куки

include("header.php");
include_once '../lang_'.$lang.'.php';

	mysql_query("SET session character_set_server = 'UTF8'");
	mysql_query("SET session character_set_connection = 'UTF8'");
	mysql_query("SET session character_set_client = 'UTF8'");
	mysql_query("SET session character_set_results = 'UTF8'");




$siteid = $_GET['siteid'];

//заполняем шапку
$sqljournaltitle = "SELECT * FROM magazines where magazines.siteid = '$siteid'";
$resjournaltitle = mysql_query($sqljournaltitle);
$rowjournaltitle=mysql_fetch_assoc($resjournaltitle);
$magazine = stripslashes($rowjournaltitle['Magazine']);
$issnp = stripslashes($rowjournaltitle['ISSNP']);
$issne = stripslashes($rowjournaltitle['ISSNE']);
$description = stripslashes($rowjournaltitle['Description']);
$publisher = stripslashes($rowjournaltitle['Publisher']);

echo "<table width=1000 cellspacing=1 cellpadding=1 border = 1 align=center>
<tr><td width=200><b>".$LANG_MESS_58.":</b></td><td width=800>$magazine</td></tr>
<tr><td width=200><b>ISSN".$LANG_MESS_69.":</b></td><td width=800>$issnp</td></tr>
<tr><td width=200><b>ISSN".$LANG_MESS_70.":</b></td><td width=800>$issne</td></tr>
<tr><td width=200><b>".$LANG_MESS_72.":</b></td><td width=800>$description</td></tr>
<tr><td width=200><b>".$LANG_MESS_9.":</b></td><td width=800>$publisher</td></tr>
<tr><td width=200><b>".$LANG_MESS_71.":</b></td><td width=800><a href='../scimag/journallinks.php?siteid=$siteid'>DOI</a></td></tr>
</table><br>";


//заполняем таблицу
$sqljournaltable = "select siteid, scimag.year,
GROUP_CONCAT(distinct(concat('v:', scimag.volume, ';i:', scimag.issue))
ORDER BY scimag.year,  scimag.volume +0, scimag.volume, scimag.issue +0, scimag.issue separator '|') as numbers
from scimag where
scimag.siteid = '$siteid'
GROUP BY scimag.siteid, scimag.year
order by scimag.siteid, scimag.year";


//шапка для таблицы 
echo "<table width=1000 cellspacing=1 cellpadding=1 border = 1 align=center>
<thead><tr>
<td width=40><b>".$LANG_MESS_10."</b></td>
<td><b>".$LANG_MESS_56."</b></td>
</tr></thead>";

//обрабатываем строки
$resjournaltable = mysql_query($sqljournaltable);
while ($rowjournaltable=mysql_fetch_assoc($resjournaltable)) {
                                                        $year = stripslashes($rowjournaltable['year']);
                                                        $issues = stripslashes($rowjournaltable['numbers']);



$issue = explode('|', $issues);
foreach($issue as $issuearr){

$issuewithtagarr[] = "<td><a href='../scimag/index.php?s=&siteid=".$siteid."".str_replace(';', '', str_replace('v:', '&v=', str_replace('i:', '&i=',  $issuearr)))."'>".str_replace('v:', 'Vol.:', str_replace('i:', 'iss.:',  $issuearr))."</a></td>";

//

}
unset($issuearr);
$issuewithtag = implode('', $issuewithtagarr);


$line = "<tr><td width=40>$year</td>$issuewithtag</tr>";
unset($issuewithtagarr);

echo $line;
}
echo "</table>";


?>