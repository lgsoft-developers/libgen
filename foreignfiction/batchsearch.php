<?php
//define('SITE_ROOT',$_SERVER['DOCUMENT_ROOT']);
$dbhost='127.0.0.1';
$dbname='fiction';
$dbuser='root';
$dbpass='';
$db='fiction';   

        $con = mysql_connect($dbhost,$dbuser,$dbpass);
	if (!$con)
		die($htmlhead."Could not connect to the database: ".mysql_error());
	mysql_query("SET session character_set_server = 'UTF8'");
	mysql_query("SET session character_set_connection = 'UTF8'");
	mysql_query("SET session character_set_client = 'UTF8'");
	mysql_query("SET session character_set_results = 'UTF8'");
	mysql_select_db($db,$con);




 echo "<table width=1024 cellspacing=1 cellpadding=1 rules=rows align=center>
<thead><tr>
<td width=500><b>Исходная строка</b></td>
<td width=460><b>Итоговая строка+ссылка</b></td>
<td width=40><b>Найдено</b></td>
</tr></thead>";


@$resp = $_POST['dsk'];
@$wordminlength = $_POST['wordminlength'];
@$stopwords = $_POST['stopwords'];
@$translit = $_POST['translit'];
@$skobki = $_POST['skobki'];
@$raschirenie = $_POST['raschirenie'];
@$md5hash = $_POST['md5hash'];

//echo $stopwords;
$stopwords = str_replace(' ','',$stopwords);
$stopwords = mb_strtolower(trim($stopwords), 'UTF8');

$stopwords = explode(",", $stopwords);



$a1=explode("\r\n", $resp);
$a1 = array_slice($a1,0,300000);

foreach ($a1 as $a9) {


if($skobki){

$a0 = strip_tags(str_replace(array('{','}'), array('<','>'), str_replace(array('(',')'), array('<','>'), str_replace(array('[',']'), array('<','>'), $a9))));

}else{$a0 = $a9;}



if($raschirenie){
$pos = mb_strrpos($a0, ".", 'UTF-8');

if(!$pos){$pos = mb_strlen($a0, 'UTF-8');}

$a0 = mb_substr($a0, 0, $pos, 'UTF-8');
}


if($translit){
$tbl= array(
'shch' => 'Щ',
'yo' => 'Ё',
'zh' => 'Ж',
'j#' => 'Й',
'ch' => 'Ч',
'sh' => 'Ш',
'e#' => 'Э',
'ju' => 'Ю',
'ja' => 'Я',
'a' => 'А',
'b' => 'Б',
'v' => 'В',
'g' => 'Г',
'd' => 'Д',
'e' => 'Е',
'z' => 'З',
'i' => 'И',
'k' => 'К',
'l' => 'Л',
'm' => 'М',
'n' => 'Н',
'o' => 'О',
'p' => 'П',
'r' => 'Р',
's' => 'С',
't' => 'Т',
'u' => 'У',
'f' => 'Ф',
'h' => 'Х',
'c' => 'Ц',
'~' => 'ъ',
'`' => 'ь',
'y' => 'Ы');
$a0 = mb_strtolower($a0, 'UTF8');
$a0 = strtr($a0, $tbl);
}




$a4 = preg_replace('/[[:punct:]]+/u', ' ', $a0);                
$a5 = preg_replace('/[\s]+/u',' ',$a4);
$a6 = mb_strtolower(trim($a5), 'UTF8');
$a2 = explode(" ", $a6);

for($i = 0, $c = count($a2); $i < $c; $i++)
{
    if(mb_strlen($a2[$i], 'UTF-8') <= $wordminlength)
        unset($a2[$i]);
}

//print_r($a2);


if($stopwords!='')
{
$a2 = array_diff($a2, $stopwords);
$a2 = implode(' ', $a2);
$a2 = preg_replace('/[\s]+/u',' ',$a2);
$a2 = explode(' ', $a2);
}



//print_r($a2);


foreach ($a2 as $a3){


if($md5hash){$matchparts[] = "MATCH(`md5`) AGAINST('$a3*' IN BOOLEAN MODE)";}
  else{
    $matchparts[] = "MATCH(`AuthorFamily1`,`AuthorName1`,`AuthorSurname1`,
`AuthorFamily2`,`AuthorName2`,`AuthorSurname2`,
`AuthorFamily3`,`AuthorName3`,`Extension`,
`AuthorFamily4`,`AuthorName4`,
`Title`,`Series1`,`Series2`,`Series3`,`Series4`) AGAINST('$a3*' IN BOOLEAN MODE)";
       }
    }
   $sql = join(' and ', $matchparts);
   unset($matchparts);




$sql = "SELECT COUNT(*) FROM main WHERE ($sql)";
//echo $sql;
$result = mysql_query($sql,$con);
$row = mysql_fetch_assoc($result);
$totalrows = stripslashes($row['COUNT(*)']);



if($md5hash){
$getparameters = '&column[]=md5';}
else{$getparameters = ''; }

$a7 = implode(' ', $a2);
$a7 = str_replace(' ', '+', $a7);
echo "<tr><td width=500>$a9</td><td width=460><a href='http://libgen.org/foreignfiction/?s=$a7' title='$a7'>$a7</a></td><td width=40>$totalrows</td></tr>";



}


mysql_close($con);
?>
