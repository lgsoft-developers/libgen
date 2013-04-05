<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
<html>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
</head>
<body>
<link rel="stylesheet" type="text/css" href="ns_tooltip.css" />
<script type="text/javascript" src="ns_tooltip.js"></script>
<script type='text/javascript'>
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-18056347-1']);
  _gaq.push(['_trackPageview']);
  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>


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

include 'config.php';
//include 'header.php';
include_once '../lang_'.$lang.'.php';
include_once 'menu_'.$lang.'.html';

echo '<table width="1000"><tr><td colspan="2" align="center"><font color=#A00000><h1><a href="/">Library Genesis:</a> Foreign fiction <sup><font size=4>800k</font></sup></h1></font></td></tr></table>';

function get_var($var,$type,$def_value){
	if (isset($_GET[$var])) $value=$_GET[$var];
	if (isset($_POST[$var])) $value=$_POST[$var];
	//echo "value=$value ";
	if (!isset($value)) $value = $def_value;
	//echo "value=$value ";
	if ($type=='int'){
	  $value=(int)$value;
		if (!preg_match('/^\d+$/',$value)) $value=$def_value;
		}
	//echo "value=$value ";
	if ($type=='text'){
		$value=htmlspecialchars($value);
		$value=mysql_real_escape_string($value);
	
		}
	return $value;
	}




function trimarray($trimstrings)
{$trimstrings = preg_replace('~[\;|\,|\:|\$|\\\|\/|\@|\#|\№|\-|\ |\.\[|\(|\{|\|]{0,8}$~isU', '', $trimstrings);
$trimstrings = preg_replace('~^[\;|\,|\:|\$|\\\|\/|\@|\#|\№|\-|\ |\.\]|\)|\}|\|]{0,8}~isU', '', $trimstrings);
return $trimstrings;}




$s=get_var("s","text",'');
$page=get_var("page","int",'1');
$items_on_page=25;

$f_lang=get_var('f_lang','int',0);
if (isset($_GET['f_columns'])) $f_columns=$_GET['f_columns']; else $f_columns =array('Author','Title','Series');
$f_cols=get_var('f_cols','text',''); if ($f_cols!='') $f_columns=preg_split('/:/',$f_cols);


echo '<form name="search" method="get">'."\n";
echo '<table width=1000 align=center border=0>'."\n";

echo '<tr><td colspan=2><input type=edit size=130 name="s" value="'.$s.'"><input type="submit" value="'.$LANG_SEARCH_0.'"><br><font face=Arial color=gray size=1><a href="batchsearchindex.php">'.$LANG_MESS_0.'</a></font></td><td></td></tr>';

$langs = array('0'=>'All','1'=>'English','2'=>'Italian','3'=>'Polish','4'=>'Spanish','5'=>'French','6'=>'Dutch','7'=>'Portuguese','8'=>'German','9'=>'Romanian');

$form_lang='';
foreach ($langs as $value=>$name) {
	//print "$name -> $value <br>";
	$form_lang.='<input type="radio" name="f_lang" value="'.$value.'" '; if ($value==$f_lang) $form_lang.=" checked=true "; $form_lang.='>'.$name.'&nbsp;';
} 
$form_lang='<tr><td colspan=2><b>'.$LANG_MESS_76.':</b>'.$form_lang.'</td><td></td></tr>'; 
echo $form_lang;


$searchf=array('Author'=>'AuthorFamily1,AuthorName1,AuthorSurname1,RussianAuthorFamily,RussianAuthorName,RussianAuthorSurname,AuthorFamily2,AuthorName2,AuthorSurname2,AuthorFamily3,AuthorName3,AuthorSurname3,AuthorFamily4,AuthorName4,AuthorSurname4','Title'=>'Title','Series'=>'Series1,Series2,Series3,Series4');
$form_searchf='';
$sql_columns='Language,Identifier';
foreach ($searchf as $value=>$name) {
	//print "$name -> $value <br>";
	$form_searchf.='<input type="checkbox" name="f_columns[]" value="'.$value.'" '; 
	if (in_array($value,$f_columns)) {$form_searchf.=' checked="true" '; $sql_columns.=','.$name;} 
	$form_searchf.=' >'.$value.'&nbsp;';
} 
$form_searchf='<tr><td><b>'.$LANG_MESS_4.'</b>'.$form_searchf.' </td><td><b>'.$LANG_MESS_165.':</b> <input name="group"  VALUE=1 type="checkbox" checked /></td></tr>'; 


echo $form_searchf; 

echo '</table>';
echo '</form>';

$group = get_var("group", "text", '0');


$s = htmlspecialchars($s,ENT_QUOTES);
$s = addcslashes(mysql_real_escape_string($s),"%_");
$s = preg_replace("/[[:punct:]]+/u", " ", $s);   
$s = preg_replace("/[\s]+/u", " ", $s);             
$s = trim($s);

if ($s!='') { 
	$words=preg_split("/ /",$s);
	$w = array (); 
		foreach ($words as $word) {
			array_push($w," MATCH(`AuthorFamily1`,`AuthorName1`,`AuthorSurname1`,`AuthorFamily2`,
                                              `AuthorName2`,`AuthorSurname2`,`AuthorFamily3`,`AuthorName3`,
                                              `AuthorFamily4`,`AuthorName4`,`Series1`,`Series2`,`Series3`,`Series4`,`Title`) 
                                               AGAINST ('".$word."*'  IN BOOLEAN MODE) ");
		}
	$sql_world = join(" AND ",$w);


if($group==0){$group_cols = "`MD5`";}else{$group_cols = "`AuthorFamily1`, `Title`, `Language`,`Series1`";}


$sql = "SELECT Concat(".$group_cols."), 
`AuthorFamily1`, `AuthorName1`, `AuthorSurname1`, `AuthorFamily2`, `AuthorName2`, `AuthorSurname2`, 
`AuthorFamily3`, `AuthorName3`, `AuthorSurname3`, `AuthorFamily4`, `AuthorName4`, `AuthorSurname4`, 
`Title`, `Series1`, `Series2`, `Series3`, `Series4`, `Language`,  
GROUP_CONCAT(MD5 ORDER BY ID SEPARATOR ',') AS `md5array` 
FROM main WHERE ".$sql_world;



if ($f_lang!=0) $sql.=" AND `Language`='".$langs[$f_lang]."' ";
$sql.=" Group By Concat(".$group_cols.") "; //order by `Title`";





//echo $sql;

	$res=mysql_query($sql, $mysql); //echo $sql." ".mysql_error()."<br>\n";


	$cn=mysql_num_rows($res);
        if ($cn>2000) $cn=2000;
	echo '<br>'.$LANG_MESS_77.': '.$cn.'<br>';
	$pages=ceil($cn/$items_on_page);
	$nav="";
	for($i=0;$i<$pages;$i++){
		$nav.='<a href="?s='.$s;
		//foreach ($f_columns as $col)
		 
		$nav.='&f_cols='.join(':',$f_columns);
		//foreach  ($f_lang as $lan) $nav.='&f_lang='.$lan;
		$nav.='&f_lang='.$f_lang;
		$nav.='&group='.$group;                
		$nav.='&page='.($i+1).'">'.($i+1).'</a>&nbsp;';
		if ($i==50) $nav.='<br>';
		if ($i==100) $nav.='<br>';

	}
	$sql.=" limit ".(($page-1)*$items_on_page).",$items_on_page";
	$res=mysql_query($sql,$mysql); //echo $sql." ".mysql_error()."<br>\n";
	if($pages>1) echo "<p>".$LANG_MESS_61.": $nav</p>";
	$i=0; $links="";


$tabheader="<table cellspacing=1 cellpadding=1 rules=rows align=center><tr><td width=200><b>".$LANG_MESS_6."</b></td><td width=100><b>".$LANG_MESS_7."</b></td><td width=400><b>".$LANG_MESS_5."</b></td><td width=70><b>".$LANG_MESS_11."</b></td><td width=280><b>".$LANG_MESS_75."</b></td></tr></table>";
echo $tabheader;
echo "<table cellspacing=1 cellpadding=1 rules=rows align=center>";

	while ($row=mysql_fetch_assoc($res)) {

//		if ($i>=(($page-1)*$items_on_page) and  ($i<$page*$items_on_page)) {

  
                                                        $Title = htmlspecialchars($row['Title'], ENT_QUOTES);
                                                        $AuthorFamily1 = htmlspecialchars($row['AuthorFamily1'], ENT_QUOTES);
                                                        $AuthorName1 = htmlspecialchars($row['AuthorName1'], ENT_QUOTES);
                                                        $AuthorSurname1 = htmlspecialchars($row['AuthorSurname1'], ENT_QUOTES);
                                                        $AuthorFamily2 = htmlspecialchars($row['AuthorFamily2'], ENT_QUOTES);
                                                        $AuthorName2 = htmlspecialchars($row['AuthorName2'], ENT_QUOTES);
                                                        $AuthorSurname2 = htmlspecialchars($row['AuthorSurname2'], ENT_QUOTES);
                                                        $AuthorFamily3 = htmlspecialchars($row['AuthorFamily3'], ENT_QUOTES);
                                                        $AuthorName3 = htmlspecialchars($row['AuthorName3'], ENT_QUOTES);
                                                        $AuthorSurname3 = htmlspecialchars($row['AuthorSurname3'], ENT_QUOTES);
                                                        $AuthorFamily4 = htmlspecialchars($row['AuthorFamily4'], ENT_QUOTES);
                                                        $AuthorName4 = htmlspecialchars($row['AuthorName4'], ENT_QUOTES);
                                                        $AuthorSurname4 = htmlspecialchars($row['AuthorSurname4'], ENT_QUOTES);
                                                        $Language = htmlspecialchars($row['Language'], ENT_QUOTES);
                                                        $Series1 = htmlspecialchars($row['Series1'], ENT_QUOTES);
                                                        $Series2 = htmlspecialchars($row['Series2'], ENT_QUOTES);    
                                                        $Series3 = htmlspecialchars($row['Series3'], ENT_QUOTES); 
                                                        $Series4 = htmlspecialchars($row['Series4'], ENT_QUOTES);                                               

                                                        $md5array = htmlspecialchars($row['md5array'], ENT_QUOTES);


$md5links = explode(',', $md5array);



for($x = 0, $e = count($md5links); $x < $e; $x++){



$sqlforeach = "SELECT * FROM main WHERE MATCH(`md5`) AGAINST('$md5links[$x]*' IN BOOLEAN MODE)";
//echo $sqlforeach;
$resforeach=mysql_query($sqlforeach);
$rowforeach=mysql_FETCH_ASSOC($resforeach);


$titleforeach = str_replace('"', '', str_replace("'", "", stripslashes($rowforeach['Title'])));
$idforeach = stripslashes($rowforeach['ID']);
$extensionforeach = stripslashes($rowforeach['Extension']);
$md5foreach = stripslashes($rowforeach['MD5']);


		$filesizeforeach = $rowforeach['Filesize'];
		if ($filesizeforeach >= 1024*1024*1024){
			$filesizeforeach = round($filesizeforeach/1024/1024/1024);
			$filesizeforeach = $filesizeforeach.''.$LANG_MESS_GB;
		} else
		if ($filesizeforeach >= 1024*1024){
			$filesizeforeach = round($filesizeforeach/1024/1024);
			$filesizeforeach = $filesizeforeach.''.$LANG_MESS_MB;
		} else
		if ($filesizeforeach >= 1024){
			$filesizeforeach = round($filesizeforeach/1024);
			$filesizeforeach = $filesizeforeach.''.$LANG_MESS_KB;
		} else
			$filesizeforeach = $filesizeforeach.''.$LANG_MESS_B;



$authorfamily1foreach = htmlspecialchars($rowforeach['AuthorFamily1'], ENT_QUOTES);
$authorfamily2foreach = htmlspecialchars($rowforeach['AuthorFamily2'], ENT_QUOTES);
$authorfamily3foreach = htmlspecialchars($rowforeach['AuthorFamily3'], ENT_QUOTES);
$authorfamily4foreach = htmlspecialchars($rowforeach['AuthorFamily4'], ENT_QUOTES);

$authorname1foreach = htmlspecialchars($rowforeach['AuthorName1'], ENT_QUOTES);
$authorname2foreach = htmlspecialchars($rowforeach['AuthorName2'], ENT_QUOTES);
$authorname3foreach = htmlspecialchars($rowforeach['AuthorName3'], ENT_QUOTES);
$authorname4foreach = htmlspecialchars($rowforeach['AuthorName4'], ENT_QUOTES);

$authorsurname1foreach = htmlspecialchars($rowforeach['AuthorSurname1'], ENT_QUOTES);
$authorsurname2foreach = htmlspecialchars($rowforeach['AuthorSurname2'], ENT_QUOTES);
$authorsurname3foreach = htmlspecialchars($rowforeach['AuthorSurname3'], ENT_QUOTES);
$authorsurname4foreach = htmlspecialchars($rowforeach['AuthorSurname4'], ENT_QUOTES);


$author1foreach = trimarray($authorfamily1foreach.', '.$authorname1foreach.' '.$authorsurname1foreach);
$author2foreach = trimarray($authorfamily2foreach.', '.$authorname2foreach.' '.$authorsurname2foreach);
$author3foreach = trimarray($authorfamily3foreach.', '.$authorname3foreach.' '.$authorsurname3foreach);
$author4foreach = trimarray($authorfamily4foreach.', '.$authorname4foreach.' '.$authorsurname4foreach);

$series1foreach = htmlspecialchars($rowforeach['Series1'], ENT_QUOTES);
$series2foreach = htmlspecialchars($rowforeach['Series2'], ENT_QUOTES);
$series3foreach = htmlspecialchars($rowforeach['Series3'], ENT_QUOTES);
$series4foreach = htmlspecialchars($rowforeach['Series4'], ENT_QUOTES);
$seriesallforeach = trimarray($series1foreach.', '.$series2foreach.', '.$series3foreach.', '.$series4foreach);

$languageforeach = htmlspecialchars($rowforeach['Language'], ENT_QUOTES);
$pagesforeach =    htmlspecialchars($rowforeach['Pages'], ENT_QUOTES);
$identifierforeach = htmlspecialchars($rowforeach['Identifier'], ENT_QUOTES);
$commentaryforeach = htmlspecialchars($rowforeach['Commentary'], ENT_QUOTES);
$yearforeach = htmlspecialchars($rowforeach['Year'], ENT_QUOTES);
$publisherforeach = htmlspecialchars($rowforeach['Publisher'], ENT_QUOTES);

if (stripslashes($rowforeach['Cover']) == 0)
{$coverforeach = 'blank.png';}
else{$coverforeach = '../foreignfiction/fiction/covers/'.substr($idforeach, 0,-3).'000/'.$md5links[$x].'.jpg';}

$wind[] = '<div id="1" style="DISPLAY: inline"; onmouseover="AddTT(\'<table width=600 border = 0 rules = rows><tr><td  ROWSPAN=12><img src='.$coverforeach.' height=300></td><td>Title:</td><td>'.$titleforeach.'</td></tr><tr><td>Author1:</td><td>'.$author1foreach.'</td></tr><tr><td>Author2:</td><td>'.$author2foreach.'</td></tr><tr><td>Author3:</td><td>'.$author3foreach.'</td></tr><tr><td>Author4:</td><td>'.$author4foreach.'</td></tr><tr><td>Series:</td><td>'.$seriesallforeach.'</td></tr><tr><td>Year:</td><td>'.$yearforeach.'</td></tr><tr><td>Publisher:</td><td>'.$publisherforeach.'</td></tr><tr><td>Identifier:</td><td>'.$identifierforeach.'</td></tr><tr><td>Pages:</td><td>'.$pagesforeach.'</td></tr><tr><td>Year:</td><td>'.$yearforeach.'</td></tr><tr><td>Language:</td><td>'.$languageforeach.'</td></tr><tr><td ROWSPAN=2 COLSPAN=3><b>Commentary:</b>'.$commentaryforeach.'</td></tr></table>\');" onmouseout="RemoveTT();"><a href="../foreignfiction/get.php?md5='.$md5links[$x].'" id="mlink" title="libgen.org">'.$extensionforeach.'('.$filesizeforeach.')</a><a href="http://fiction.libgen.net/view.php?md5='.$md5links[$x].'" id="mlink" title="libgen.net">[1]</a>&#9;</div>';







}
$windall = implode('',$wind);
unset($wind);






$Auth1 = trimarray($AuthorFamily1.', '.$AuthorName1.' '.$AuthorSurname1);
$Auth2 = trimarray($AuthorFamily2.', '.$AuthorName2.' '.$AuthorSurname2);
$Auth3 = trimarray($AuthorFamily3.', '.$AuthorName3.' '.$AuthorSurname3);
$Auth4 = trimarray($AuthorFamily4.', '.$AuthorName4.' '.$AuthorSurname4);
$a = array_filter(array(1=>$Auth1, 2=>$Auth2, 3=>$Auth3, 4=>$Auth4));
$a = join('<br>', $a);
$a = '<tr><td width=200>'.$a.'</td>';
$line = "$a<td width=100>$Series1 $Series2 $Series3 $Series4</td>

<td width=400>$Title</td>
<td width=70>$Language</td>
<td width=280>$windall</td>
</tr>";
echo $line;

	$links.=stripslashes($row['Title'])."\n";
	$i++;	
	}
        echo "</table>";
	$start=($page-1)*$items_on_page;
	$end=$start+$items_on_page;

	echo "<p>".$LANG_MESS_61.": $nav</p>";
}


?>

</body>
</html>
