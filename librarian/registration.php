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

include_once '../lang_'.$lang.'.php';
include 'html.php';
echo $htmlheadfocus;
include_once '../menu_'.$lang.'.html';



// if MD5 provided, go straight to the editor form
if (isset($_GET['md5']) && $_GET['md5'] != ''){
	$_POST['MD5'] = $_GET['md5'];
	$_POST['Form'] = 2;
	include 'form.php';
	exit(0);
}




$page = "
<center><table border=1 cellspacing=0 cellpadding=12 bordercolor='#A00000'>
<caption><font color='#A00000'><h1><a href='../'>Library Genesis</a>: Librarian</h1></font><br>
".$LANG_MESS_84." <a href = '../batchsearchindex.php'>".$LANG_MESS_85."</a>".$LANG_MESS_86."</caption>

<!-- File-Upload Form -->
<tr><td><form enctype='multipart/form-data' action='form.php' method='POST'>
<input type='hidden' name='MAX_FILE_SIZE' value='350000000'/>
<input type='hidden' name='Form' value='1'/>
<input type='hidden' name='MD5' value=''/>
".$LANG_MESS_79.":<br>
<input name='uploadedfile' type='file' size=120/> 
<input type='submit' value='".$LANG_MESS_88."'/><br>
<font face=Arial color=gray size=1>".$LANG_MESS_80."</font></form></td></tr>


<!-- File-DL from remote server Form -->
<tr><td><form enctype='multipart/form-data' action='form.php' method='POST'>
<input type='hidden' name='MAX_FILE_SIZE' value='200000000'/>
<input type='hidden' name='Form' value='3'/>
<input type='hidden' name='MD5' value=''/>
".$LANG_MESS_78.":<br>
<input name='uploadedfile' type='text' size=120/> 
<input type='submit' value='".$LANG_MESS_88."'/><br>
<font face=Arial color=gray size=1><a href = 'ftp://libgen.org/!upload/'>".$LANG_MESS_90."</a></font></form></td></tr>


<!-- MD5-check-up Form -->
<tr><td><form enctype='multipart/form-data' action='form.php' method='POST'>
<input type='hidden' name='Form' value='2'/>
".$LANG_MESS_91.":<br><input name='MD5' id='1' type='text' size=120 maxlength=32/> <input type='submit' value='".$LANG_MESS_89." MD5!'/>
<br><font face=Arial color=gray size=1>".$LANG_MESS_81."</font></form></td></tr>


</table>
<font face=Arial size=2>
".$LANG_MESS_87."<br>
".$LANG_MESS_82.": <a href='http://magzDB.org/'>magzDB.org</a> ; <br>
".$LANG_MESS_400.": <a href='http://libgen.org/scimag/librarian/'>Library Genesis: Scientific articles</a> ; <br>
".$LANG_MESS_83.": <a href='http://flibusta.net/'>flibusta.net</a> & <a href='http://lib.rus.ec/'>lib.rus.ec</a></font>
</center>";

echo $page;


echo $htmlfoot;

?>
