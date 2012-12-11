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







$letterjournal = $_GET['letter'];


//echo $letterjournal;



if ($letterjournal == '0-9'){
$sqljournal = "SELECT distinct magazines.siteid,
magazines.magazine, magazines.issnp, magazines.issne
FROM magazines
where magazines.magazine rlike '^[0-9]' and 
magazines.siteid in (select distinct scimag.siteid from scimag where magazines.siteid != '')
order by magazines.magazine";
//echo $sqljournal;
} elseif($letterjournal == 'Other'){
$sqljournal = "SELECT distinct magazines.siteid,
magazines.magazine, magazines.issnp, magazines.issne
FROM magazines
where magazines.magazine not rlike '[0-9A-Za-z]' and
magazines.siteid in (select distinct magazines.siteid from scimag where magazines.siteid !='')
order by magazines.magazine";
//echo $sqljournal;
}else{


$sqljournal = "SELECT distinct magazines.siteid,
magazines.magazine, magazines.issnp, magazines.issne
FROM magazines
where magazines.magazine like '$letterjournal%' and 
magazines.siteid in (select distinct scimag.siteid from scimag where scimag.siteid !='')
order by magazines.magazine";
}





//echo $sqljournal;

$resjournal = mysql_query($sqljournal); 

 echo "<table width=1000 cellspacing=1 cellpadding=1 rules=rows align=center>


  <thead><tr>
<td width=700><b>".$LANG_MESS_5."</b></td>
<td width=100><b>ISSN ".$LANG_MESS_69."</b></td>
<td width=100><b>ISSN ".$LANG_MESS_70."</b></td>
 </tr></thead>";

while ($rowjournal=mysql_fetch_assoc($resjournal)) {
                                                        $issnp = stripslashes($rowjournal['issnp']);
                                                        $issne = stripslashes($rowjournal['issne']);
                                                        $journal = stripslashes($rowjournal['magazine']);
                                                        $siteid = stripslashes($rowjournal['siteid']);


$linkjournal = "journaltable.php?siteid=$siteid";
$line = "<tr><td width=700><a href=$linkjournal>$journal</a></td>
<td width=150>$issnp</td>
<td width=150>$issne</td></tr>";
echo $line;


}
echo "</table>";

?>