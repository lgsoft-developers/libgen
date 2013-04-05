<!DOCTYPE html>
<html>
<head>
<meta http-equiv='content-type' content='text/html; charset=utf-8'>
<title>Batch search for Library Genesis fiction books</title>
</head>
<body>
<?php

    // Установка куки для запоминания выбора языка
    if(isset($_COOKIE['lang'])) { 
       $lang = $_COOKIE['lang'];
       $lang_file = 'lang_'.$lang.'.php';
       if(!file_exists($lang_file)) { $lang_file = 'lang_en.php'; }
    } else {
         $lang = 'en';
         $lang_file = '../lang_en.php';
       }
     // -- Конец установки куки

        include_once '../lang_'.$lang.'.php';
        include_once 'menu_'.$lang.'.html';



/*$page = "
<center><table width = 1000 border=1 cellspacing=0 cellpadding=12 bordercolor='#A00000'>
<caption><font color='#A00000'><h1>Batch search for fiction books</h1></font><br></caption>
<!-- File-DL from remote server Form -->
<tr><td>
<FORM name='filenames' enctype='multipart/form-data' METHOD='POST' ACTION='batchsearch.php'>
<table  cellspacing=0 border=0 width=100% height=100%>
<col width='50%'>
<col width='50%'>

<tr><td nowrap valign='middle' align='right'>Убрать из строки слова короче или равные N буквам:</td><td nowrap><select name='wordminlength' size='1'>
<option value='0'>0</option>
<option value='1'>1</option>
<option value='2'>2</option>
<option value='3'>3</option>
<option value='4'>4</option>
<option value='5'>5</option>
<option value='6'>6</option>
<option value='7'>7</option>
<option value='8'>8</option>
<option value='9'>9</option>
</select>Remove from the string words shorter than or equal to N letter</td></tr>

<tr><td nowrap valign='middle' align='right'>Убрать слова в скобках ()[]{}: </td><td nowrap><input name='skobki'  VALUE='1' type='checkbox' />Remove the words in brackets ()[]{}</td></tr>
<tr><td nowrap valign='middle' align='right'>Удалить расширение (все что после последней точки): </td><td nowrap><input name='raschirenie'  VALUE='1' type='checkbox' />Remove extension (everything after the last dot)</td></tr>
<tr><td nowrap valign='middle' align='right'>Транслитерировать (LAT-RUS, kolxo3): </td><td nowrap><input name='translit'  VALUE='1' type='checkbox' />Transliterate (LAT-RUS, kolxo3)</td></tr>
<tr><td nowrap valign='middle' align='right'>Искать MD5 хеш (по 1 MD5 в строке): </td><td nowrap><input name='md5hash'  VALUE='1' type='checkbox' />Search only MD5 hash (to one MD5 in string)</td></tr>
<tr><td nowrap valign='middle' align='right'>Убрать из строки слова (перечислить через ',' (максимум 100 знаков)) <br>Remove words from a string (list through ',' (maximum 100 characters)):</td><td nowrap><input name='stopwords' type='text' size=80 maxlength=100/></td></tr>
<tr><td valign='middle' align='left' colspan=2>Введите строки (максимум 500) \ Enter string (max 500):</td></tr>
</table>
<div><textarea id='teTestCode' name='dsk' rows='19' cols='120'></textarea>
<br>Пунктуация удаляется, все переводится в нижний регистр, поиск по полям: Заглавие, Автор, Серия<br>
Punctuation is removed, all translated to lower case, search the following fields: Title, Author, Series</div>
<div><INPUT TYPE='submit' name='submit' value='Искать'></div>



</FORM>
</td></tr>
</table>
</center>";*/


$page = "<table width=1024 border=1 cellspacing=0 cellpadding=0 bordercolor='#A00000' align=center>
<caption><font color='#A00000'><h1>Batch search for <a href='/'>Library Genesis</a> fiction books</h1></font><br></caption>
<tr><td><FORM name='filenames' enctype='multipart/form-data' METHOD='POST' ACTION='batchsearch.php'>
<table  cellspacing=0 border=0 width=1000 height=100% align=center>
<tr><td><INPUT TYPE='submit' name='submit' value='".$LANG_SEARCH_0."'></td><td>".str_replace('50', 500, $LANG_MESS_120).":</td></tr>
<tr><td><select name='wordminlength' size='1'>
<option value='0'>0</option>
<option value='1'>1</option>
<option value='2'>2</option>
<option value='3'>3</option>
<option value='4'>4</option>
<option value='5'>5</option>
<option value='6'>6</option>
<option value='7'>7</option>
<option value='8'>8</option>
<option value='9'>9</option>
</select>".$LANG_MESS_129."<hr></td><td rowspan=7><div><textarea id='teTestCode' name='dsk' rows='20' cols='80'></textarea></div></td></tr>
<tr><td><input name='skobki'  VALUE='1' type='checkbox' />".$LANG_MESS_123." ()[]{}<hr></td></tr>
<tr><td><input name='raschirenie'  VALUE='1' type='checkbox' />".$LANG_MESS_122." (.*)<hr></td></tr>
<tr><td><input name='stopwords' type='text' size=40 maxlength=100/><br>".$LANG_MESS_124."<hr></td></tr>


<tr><td></td><td></td></tr>
</table>
</FORM>
</td></tr>
</table>";


echo $page;
?>
</body></html>