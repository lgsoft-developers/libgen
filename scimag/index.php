<?php 

set_time_limit(20); 
ob_start();

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

$s=get_var("s","text",'');
$page=get_var("page","int",'1');
$items_on_page=25;

if(isset($_GET['v'])){$volumesearch = $_GET['v'];}
else{$volumesearch = '';}

if(isset($_GET['i'])){$issuesearch = $_GET['i'];}
else{$issuesearch = '';}

if(isset($_GET['siteid'])){$siteid = $_GET['siteid'];}
else{$siteid = '';}

if(isset($_GET['last'])){$last = $_GET['last'];}
else{$last = 1;}


if($siteid == ''){$issuesearch = '';  $volumesearch = '';}



echo '<form name="search">'."\n";
echo '<table width=900 align=center>'."\n";
echo '<tr><td><input type=edit size=80 name="s" value="'.$s.'"></td>

<td><input type=edit size=15 name="siteid" value="'.$siteid.'"></td>

<td><input type=edit size=10 name="v" value="'.$volumesearch.'"></td>

<td><input type=edit size=10 name="i" value="'.$issuesearch.'"></td>

<td><input type="submit" value="'.$LANG_SEARCH_0.'"><br></td></tr>

<tr valign=top><td><font face=Arial color=gray size=1>'.$LANG_MESS_18.' <a href="http://sci-hub.org/">sci-hub.org</a></font></td>
<td><font face=Arial color=gray size=1>'.$LANG_MESS_55.'</font></td>
<td><font face=Arial color=gray size=1>'.$LANG_MESS_118.'</font></td>
<td><font face=Arial color=gray size=1>'.$LANG_MESS_56.'</font></td></tr>';
echo '</table>'."\n";
echo '</form>'."\n";

$s = mysql_real_escape_string($s);
$s = preg_replace('/[\s]+/u',' ',$s);
$s = trim($s);
$s = htmlspecialchars_decode($s);


if(isset($_GET['s'])){

if ($s!=='' )
{
   if(preg_match('(^10\.\d{4}/[\d\:\.\(\)\;\[\]\_\<\>\-\/a-zA-Z]{1,100}$)',$s))
      {
      $sql_world = " `DOI`='$s'";
      }else{
        $s = preg_replace('/[[:punct:]]+/u', ' ', $s);  
        $s = preg_replace('/[\s]+/u',' ',$s);
	$words=explode(" ",$s);

            for($i = 0, $c = count($words); $i < $c; $i++)
            {
            if(mb_strlen($words[$i], 'UTF-8') <= 3)
            unset($words[$i]);
            }
       $w = array(); 
       foreach ($words as $word) { 
       array_push($w," MATCH(`Title`,`Author`) AGAINST('$word' IN BOOLEAN MODE)");  
       $sql_world = join(" and ",$w);  
       }

      if($siteid != ''){$sql_world .= " AND `SITEID`='$siteid'";}
      if($issuesearch != ''){$sql_world .= " AND `issue`= '$issuesearch'";}       
      if($volumesearch != ''){

if(preg_match('|^[0-9]{4}$|', $volumesearch)){$sql_world .= " AND `year`= '$volumesearch'";}else{$sql_world .= " AND `volume`= '$volumesearch'";}



} 
}
 }elseif($s=='' && $siteid !== ''){
$sql_world = '';
if($siteid != ''){$sql_world .= " `SITEID`='$siteid'";}
if($issuesearch != ''){$sql_world .= " AND `issue`= '$issuesearch'";}       
if($volumesearch != ''){

if(preg_match('|^[0-9]{4}$|', $volumesearch)){$sql_world .= " AND `year`= '$volumesearch'";}else{$sql_world .= " AND `volume`= '$volumesearch'";}

} 
}


	$sql= str_replace(' WHERE AND ', ' WHERE ', "select * from `scimag` WHERE ".$sql_world."  limit 1000");

//echo $sql;

	$res=$mysql->query($sql); //echo $sql." ".mysql_error()."<br>\n";

//если ничего не найдено, и заполнен siteid - возможно siteid - название журнала, ищем siteid по нему
	if($mysql->num_rows($res) == 0 && preg_match("/SITEID/i", $sql)){

$sqlmagsiteid = "SELECT * FROM `magazines` where upper(`magazine`) LIKE upper('".str_replace(' ', '% ', $siteid)."%') order by magazine LIMIT 1";
//echo $sqlmagsiteid;
$resmagsiteid=$mysql->query($sqlmagsiteid);
if($mysql->num_rows($resmagsiteid) !==0){

$rowmagsiteid = $mysql->FETCH_ASSOC($resmagsiteid);

$siteidm = stripslashes($rowmagsiteid['SITEID']);  
//echo $siteidm;
$sql = preg_replace("/`SITEID`='(.*?)'/", "`SITEID`='".$siteidm."'", $sql);






}
}

//выводим последнее
if($last !== 1){$sql = "SELECT * FROM `scimag` ORDER BY ID DESC LIMIT 2000";}

$res=$mysql->query($sql);
//echo $sql;


	$cn=$mysql->num_rows($res);
if ($cn == 0 && preg_match('(^10\.\d{4}/[\d\:\.\(\)\;\[\]\_\<\>\-\/a-zA-Z]{1,100}$)',$s))
{
header('Location: http://dx.doi.org.sci-hub.org/'.$s, true, 301 );
}elseif ($cn == 0 && !preg_match('(^10\.\d{4}/[\d\:\.\(\)\;\[\]\_\<\>\-\/a-zA-Z]{1,100}$)',$s))
{
//header('Location: http://scholar.google.com.sci-hub.org/scholar?q='.$s, true, 301 );
}




	$pages=ceil($cn/$items_on_page);
	$nav="";
	for($i=0;$i<$pages;$i++){
     //  $s = rawurlencode($s);


                if($last !== 1){$nav.='<a href="?s=&last=&page='.($i+1).'">'.($i+1).'</a>&nbsp;';} else 
                {$nav.='<a href="?s='.$s.'&siteid='.$siteid.'&v='.$volumesearch.'&i='.$issuesearch.'&page='.($i+1).'">'.($i+1).'</a>&nbsp;';}

		if ($i==50) $nav.='<br>';
	}
	//$sql.=" limit ".(($page-1)*$items_on_page).",$items_on_page";
	$res=$mysql->query($sql); //echo $sql." ".mysql_error()."<br>\n";
	if($pages>1) {$pagination = "$LANG_MESS_61: $nav<br>";} else {$pagination = '';}
	$start=($page-1)*$items_on_page;
	$end=$start+$items_on_page;
        echo $pagination;
        echo $LANG_MESS_62." ".$start." ".$LANG_MESS_63."".$end." ".$LANG_MESS_68." ".$cn;

	$i=0; $links="";
 echo "<table width=1024 cellspacing=1 cellpadding=1 rules=rows align=center>


  <thead><tr>
<td width=130><b>DOI</b></td>
<td width=200><b>".$LANG_MESS_6."</b></td>
<td width=300><b>".$LANG_MESS_57."</b></td>
<td width=100><b>".$LANG_MESS_9."</b></td>
<td width=100><b>".$LANG_MESS_58."</b></td>
<td width=60><b>".$LANG_MESS_56."</b></td>
<td width=50><b>ISSN</b></td>
<td width=60><b>".$LANG_MESS_60."</b></td>
<td width=50><b>MD5</b></td>
 </tr></thead>";
	while ($row=$mysql->FETCH_ASSOC($res)) {


		                                        if ($i>=(($page-1)*$items_on_page) and  ($i<$page*$items_on_page)) {
                                                    //    preg_match('/ftp\:\/\/free-books.sytes.net\/(.*)$/',),$match);
if($row['ID'] < 5711857){$urlpath = '../scimag1';}
elseif ($row['ID'] > 5711856 and $row['ID'] < 10168797) {$urlpath = '../scimag2';}
elseif ($row['ID'] > 10168796 and $row['ID'] < 15157523) {$urlpath = '../scimag3';}
else {$urlpath = '../scimag4';}

$doi = stripslashes($row['DOI']);
$doimagazine=substr($doi, 8);
$doipublisher=substr($doi, 0, 8);
$doipublisher1=substr($doi, 0, 7);




                                                        $doi = '<a href="'.$urlpath.'/'.$doipublisher.''.rawurlencode(rawurlencode($doimagazine)).'.pdf">'.$doi.'</a>';
                                                        $author = stripslashes($row['Author']);
                                                        $article = stripslashes($row['Title']);  
                                                        $siteid = stripslashes($row['SITEID']);
                                                        $md5 = stripslashes($row['MD5']);                                                   
                                                      
                                                   
                                                        $year = stripslashes($row['Year']);
                                                        $month = stripslashes($row['Month']);

                                                        $fpage = stripslashes($row['First_page']);
                                                        $lpage = stripslashes($row['Last_page']);

                                                        $day = stripslashes($row['Day']);
                                                        $volume = trim(stripslashes($row['Volume']));
                                                        $issue = stripslashes($row['Issue']);
 //определяем изд. по doi
                                                        $sqlpub = 'select * from `publishers` where `DOICode`="'.$doipublisher1.'"';
                                                        $filesize = ceil($row['Filesize']/1024);
                                                        $publres = $mysql->query($sqlpub);
                                                        $rowpubl=$mysql->FETCH_ASSOC($publres);
                                                        $publisher = stripslashes($rowpubl['Publisher']);
 //определяем журн. по спрингерлинк id или по Issnp, issne       
                                                        
                                                        if ($siteid != ''){
                                                        $sqlmag = 'select * from `magazines` where `SITEID`="'.$siteid.'"'; 
//echo $sqlmag;
                                                        $magres = $mysql->query($sqlmag);
                                                        $rowmag=$mysql->FETCH_ASSOC($magres);
                                                        $magazine = stripslashes($rowmag['Magazine']);
                                                        $issnp =  stripslashes($rowmag['ISSNP']);
                                                        $issne =  stripslashes($rowmag['ISSNE']); }                                                                                                             
                                                        else { $magazinem = ''; $issnpm = ''; $issnem = ''; }


                                                        
$line = "<tr>
<td width=130>$doi</td>
<td width=200>$author</td>
<td width=300>$article</td>
<td width=100>$publisher</td>
<td width=100>$magazine</td>
<td width=60>".$LANG_MESS_10.":$year<br>
".$LANG_MESS_66.":$month<br>
".$LANG_MESS_67.":$day<br>
".$LANG_MESS_42.":$volume<br>
".$LANG_MESS_56.":$issue<br>
".$LANG_MESS_64.":$fpage<br>
".$LANG_MESS_65.":$lpage</td>
<td width=50>$issnp(p)<br>$issne(e)</td>
<td width=60>$filesize kB</td>
<td width=40><a href=''title='$md5'>MD5</a></td>
</tr>";
echo $line;


		} 
                                                        

	//$links.=stripslashes($row['ftp_path'])."\n";
	$i++;	
	}
echo "</table>";

	echo $pagination;
        echo "Записи с $start по $end из $cn";
	//echo "Ссылки на закачку: <br><table align=center><tr><td><textarea cols=120 rows=20 align=center>$links</textarea></td></tr></table>";
	
}

//mysql_close($con);

include("footer.php");

//include("sci.php");
?>