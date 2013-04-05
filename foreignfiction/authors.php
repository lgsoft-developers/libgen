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
include_once 'menu_'.$lang.'.html';

if(!isset($_GET['letter']))
	die("<font color='#A00000'><h1>Wrong Letter</h1></font>");

$letter = mysql_real_escape_string(substr(strtoupper($_GET['letter']), 0, 1));
if(!preg_match('|^[A-Za-z0-9]$|', $letter))
	die("<font color='#A00000'><h1>Wrong Letter</h1></font>");


$sqlauth = "SELECT DISTINCT AuthorFamily1,AuthorName1, AuthorSurname1, COUNT(*) FROM `main` WHERE `AuthorFamily1` LIKE '$letter%' GROUP BY AuthorFamily1,AuthorName1";
$resauth = mysql_query($sqlauth); 
echo "<br><table width=1000 align=center rules=rows><thead><tr><td width=900><b>Author</b></td><td><b>How many books</b></td></tr></thead><tbody>";
 while ($row=mysql_fetch_assoc($resauth)) {
                                                        $AuthorFamily1 = stripslashes($row['AuthorFamily1']);
                                                        $AuthorName1 = stripslashes($row['AuthorName1']);
                                                        $AuthorSurname1 = stripslashes($row['AuthorSurname1']);
                                                        $bookscount = stripslashes($row['COUNT(*)']);
                                                        $Author = str_replace(' ', '%20', ($AuthorFamily1.' '.$AuthorName1.' '.$AuthorSurname1));
$link = "index.php?s=$Author&f_lang=0&f_columns[]=Author&f_columns[]=Title&f_columns[]=Series&group=1";
$line = "<tr><td width=900><a href=$link>$AuthorFamily1, $AuthorName1 $AuthorSurname1</a></td><td>$bookscount</td>";
echo $line;
echo "</tr>";
}
echo "</tbody></table>";
?>
</body>
</html>