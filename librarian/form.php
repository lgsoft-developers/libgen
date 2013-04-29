<?php
// Установка куки для запоминания выбора языка
if (isset($_COOKIE['lang'])) {
	$lang      = $_COOKIE['lang'];
	$lang_file = 'lang_' . $lang . '.php';
	if (!file_exists($lang_file)) {
		$lang_file = 'lang_en.php';
	}
} else {
	$lang      = 'en';
	$lang_file = 'lang_en.php';
}
// -- Конец установки куки
include_once '../menu_' . $lang . '.html';
include_once '../lang_' . $lang . '.php';

ini_set('memory_limit', '200M');
include 'connect.php';
include 'html.php';
// form 1 (new record) and 2 (editing) defaults
$author           = '';
$id               = '';
$generic          = '';
$topic            = '';
$volinfo          = '';
$year             = '';
$publisher        = '';
$city             = '';
$edition          = '';
$series           = '';
$periodical       = '';
$pages            = '';
$identifier       = '';
$asin             = '';
$language         = '';
$library          = '';
$locator          = '';
$issue            = '';
$orientation      = '';
$dpi              = '';
$color            = '';
$cleaned          = '';
$commentary       = '';
$coverurl         = '';
$udc              = '';
$lbc              = '';
$bookcode         = '';
$openlibraryid    = '';
$issn             = '';
$ddc              = '';
$lcc              = '';
$googlebookid     = '';
$doi              = '';
$searchable       = '';
$bookmarked       = '';
$scanned          = '';
$paginated        = '';
$timelastmodified = '';
$timeadded        = '';
//убираем символы в начале\конце строки
function trimarray($trimstrings)
{
	$trimstrings = htmlspecialchars_decode($trimstrings);
	$trimstrings = strip_tags($trimstrings);
	$trimstrings = htmlspecialchars($trimstrings);
	$trimstrings = preg_replace('~[\;|\:|\,|\\\|\/|\@|\$|\-|\ |\>|\}|\|]{0,5}$~isU', '', $trimstrings);
	$trimstrings = preg_replace('~^[\;|\:|\,|\\\|\/|\@|\$|\-|\ |\<|\{|\|]{0,5}~isU', '', $trimstrings);
	return $trimstrings;
}
function isbntextnormalize($data)
{
	$replaceablesymbols = array(
		'standart book number ',
		"\t",
		"\r\n",
		"\n",
		')',
		'(',
		'.',
		'',
		'o',
		'о',
		'O',
		'О',
		'З',
		'l',
		',',
		':',
		'Х',
		'х',
		'x',
		'—',
		'―',
		'-',
		'‒',
		'―-',
		'--',
		'―-',
		'  ',
		' - ',
		'  ',
		'  ',
		' ',
		'isbn',
		'ISBN',
		'ISBN 10: ',
		'ISBN 13: ',
		'ISBN-10 :',
		'ISBN-13 :',
		'ISBN-13 ',
		'ISBN-10 ',
		'ISBN-10:',
		'ISBN-13:',
		'ISBN10:',
		'ISBN13:',
		'ISBN13 ',
		'ISBN10 ',
		'ISBN=',
		'ISBN :',
		'ISBN:',
		'ISBN-',
		'ISBN '
	);
	$replacingsymbols   = array(
		'ISBN',
		' ',
		' ',
		' ',
		' ',
		' ',
		'-',
		'',
		'0',
		'0',
		'0',
		'0',
		'3',
		'1',
		'-',
		' ',
		'X',
		'X',
		'X',
		'-',
		'-',
		'-',
		'-',
		'-',
		'-',
		'-',
		' ',
		'-',
		' ',
		' ',
		' ',
		'ISBN',
		'ISBN',
		'ISBN',
		'ISBN',
		'ISBN',
		'ISBN',
		'ISBN',
		'ISBN',
		'ISBN',
		'ISBN',
		'ISBN',
		'ISBN',
		'ISBN',
		'ISBN',
		'ISBN',
		'ISBN',
		'ISBN',
		'ISBN',
		'ISBN'
	);
	$data               = str_replace($replaceablesymbols, $replacingsymbols, $data);
	return $data;
}
// form 1 or 2 submitted?
if ($_POST['Form'] == 1) //new record
	{
	if (trim($_FILES['uploadedfile']['name']) == '')
		die($htmlhead . "<font color='#A00000'><h1>No file selected</h1></font>Use 'Browse...' to choose a file on your computer, then 'Send!' to upload it.<br><a href='registration.php'>Return to the last page</a> and try again!" . $htmlfoot);
	$pi       = pathinfo($_FILES['uploadedfile']['name']);
	$md5      = md5_file($_FILES['uploadedfile']['tmp_name']);
	//$title = htmlspecialchars($pi['filename'],ENT_QUOTES);
	$locator  = htmlspecialchars($pi['filename'], ENT_QUOTES);
	$locator  = str_replace("'", " ", $locator);
	$locator  = str_replace("#", "_", $locator);
	$locator  = str_replace("&", "_", $locator);
	$locator  = str_replace("$", "_", $locator);
	$filesize = $_FILES['uploadedfile']['size'];
	@$fileext = strtolower($pi['extension']);
	if ($fileext == 'djv') {
		$fileext == 'djvu';
	}
	if (is_null($fileext))
		die($htmlhead . "<font color='#A00000'><h1>Error</h1></font>Cannot upload a file with no extension. Assign one and try again!" . $htmlfoot);
	if ($fileext == 'php' || $fileext == 'css' || $fileext == 'jpg' || $fileext == 'gif' || $fileext == 'htm' || $fileext == 'html' || $fileext == 'py' || $fileext == 'pl' || $fileext == 'exe')
		die($htmlhead . "<font color='#A00000'><h1>Error</h1></font>Bad extension!" . $htmlfoot);
	//echo $fileext;
	if (copy($_FILES['uploadedfile']['tmp_name'], $md5)) {
		echo ' ';
		unset($_FILES);
	} else {
		echo $htmlhead . "<font color='#A00000'><h1>Upload failed</h1></font>There was an error uploading the file. Please try again!" . $htmlfoot;
	}
	if ($fileext == 'pdf' || $fileext == 'djvu') {
		if ($fileext == 'pdf') {
			$prog = "pdftotext.exe -l 10 ";
			$args = $prog . '' . $md5 . ' temp.txt';
			system($args);
			if (@filesize('temp.txt') >= 50) {
				$searchable = '1';
			} else {
				$searchable = '0';
			}
			$argumentspdfpages = '"pdfinfo.exe -meta "' . $md5 . '" > pdfinfo.txt"';
			@system($argumentspdfpages);
			@$pagescount = file_get_contents('pdfinfo.txt');
			preg_match('|Pages:\s{1,20}(.*?)\r\n|sei', $pagescount, $pagescountarr);
			$pages = $pagescountarr[1];
			@unlink('pdfinfo.txt');
		} elseif ($fileext == 'djvu') {
			$prog = "djvutxt.exe --page=1-10 ";
			$args = $prog . '' . $md5 . ' temp.txt';
			system($args);
			if (@filesize('temp.txt') >= 50) {
				$searchable = '1';
			} else {
				$searchable = '0';
			}
			$argumentsdjvupages = '"djvused.exe -e n "' . $md5 . '" > djvuinfo.txt"';
			@system($argumentsdjvupages);
			@$pagescount = file_get_contents('djvuinfo.txt');
			@unlink('djvuinfo.txt');
			@$pages = str_replace("\r\n", "", $pagescount);
			system('"djvudump.exe "' . $md5 . '" > djvudump.txt"');
		}
		if (($fileext == 'pdf' && ($pages == '' )) || ($fileext == 'djvu' && filesize('djvudump.txt') == 0)) {
			echo $htmlhead . "<font color='#A00000'><h1>Broken file</h1></font>" . $htmlfoot;
			exit();
		}
		@unlink('djvudump.txt');
		@$data = isbntextnormalize(file_get_contents("temp.txt"));
		//$data = iconv()
		//echo $data;
		preg_match_all('|ISBN[0-9X-]{10,17}\s|i', $data, $isbnfind);
		for ($j = 0, $c = count($isbnfind[0]); $j < $c; $j++) {
			$isbnfind[0][$j] = str_replace('ISBN', '', $isbnfind[0][$j]);
			$isbnfind[0][$j] = str_replace(' ', '', $isbnfind[0][$j]);
			if (strlen($isbnfind[0][$j]) < 13)
				unset($isbnfind[0][$j]);
			$isbnfind[0][$j] = "<OPTION VALUE='" . $isbnfind[0][$j] . "'>" . $isbnfind[0][$j] . "</OPTION>";
		}
		//print_r($isbnfind);
		$isbnfind = implode($isbnfind[0]);
		//echo $isbnfind;
	}
} elseif ($_POST['Form'] == 2) //edit record
	{
	if (strlen($_POST['MD5']) != 32)
		die($htmlhead . "<font color='#A00000'><h1>Wrong MD5</h1></font>MD5-hashsum must contain 32 symbols.<br>Check it and <a href='registration.php'>try again</a>.<p><h2>Thank you!</h2>" . $htmlfoot);
	$md5      = $_POST['MD5'];
	$title    = '';
	$filesize = 0;
	$locator  = '';
	$fileext  = '';
} elseif ($_POST['Form'] == 3) //upload from remote host
	{
	$file    = $_POST['uploadedfile'];
	$file    = iconv('UTF-8', 'WINDOWS-1251', $file);
	$file    = rawurldecode($file);
	$fileext = pathinfo(basename($file));
	$fileext = $fileext['extension'];
	if ($fileext == 'php' || $fileext == 'css' || $fileext == 'jpg' || $fileext == 'gif' || $fileext == 'htm' || $fileext == 'html' || $fileext == 'py' || $fileext == 'pl' || $fileext == 'exe' || $fileext == '')
		die($htmlhead . "<font color='#A00000'><h1>Error</h1></font>Bad extension!" . $htmlfoot);
	if (@fopen($file, "r")) {
		//echo "Файл существует";
		$ffout = fopen("from_ext_" . substr($file, strrpos($file, "/") + 1), "w");
		fwrite($ffout, file_get_contents($file));
		fclose($ffout);
		$file       = basename($file);
		$uploadfile = "C:\Program Files\Apache2\htdocs\librarian\\from_ext_" . $file;
		if ($fileext == 'pdf' || $fileext == 'djvu') {
			if ($fileext == 'pdf') {
				$prog = "pdftotext.exe -l 10 ";
				$args = $prog . '' . $uploadfile . ' temp.txt';
				system($args);
				if (@filesize('temp.txt') >= 50) {
					$searchable = '1';
				} else {
					$searchable = '0';
				}
				$argumentspdfpages = '"pdfinfo.exe -meta "' . $uploadfile . '" > pdfinfo.txt"';
				@system($argumentspdfpages);
				@$pagescount = file_get_contents('pdfinfo.txt');
				preg_match('|Pages:\s{1,20}(.*?)\r\n|sei', $pagescount, $pagescountarr);
				$pages = $pagescountarr[1];
				@unlink('pdfinfo.txt');
			} elseif ($fileext == 'djvu') {
				$prog = "djvutxt.exe --page=1-10 ";
				$args = $prog . '' . $uploadfile . ' temp.txt';
				system($args);
				if (@filesize('temp.txt') >= 50) {
					$searchable = '1';
				} else {
					$searchable = '0';
				}
				$argumentsdjvupages = '"djvused.exe -e n "' . $uploadfile . '" > djvuinfo.txt"';
				@system($argumentsdjvupages);
				@$pagescount = file_get_contents('djvuinfo.txt');
				@unlink('djvuinfo.txt');
				@$pages = str_replace("\r\n", "", $pagescount);
				system('"djvudump.exe "' . $uploadfile . '" > djvudump.txt"');
			}
			if (($fileext == 'pdf' && ($pages == '')) || ($fileext == 'djvu' && filesize('djvudump.txt') == 0)) {
				echo $htmlhead . "<font color='#A00000'><h1>Broken file</h1></font>" . $htmlfoot;
				exit();
			}
		}
		$md5      = md5_file($uploadfile);
		$filesize = filesize($uploadfile);
		$locator  = $file = iconv('WINDOWS-1251', 'UTF-8', $file);
		$locator  = str_replace("'", " ", $locator);
		$locator  = str_replace("#", "_", $locator);
		$locator  = str_replace("&", "_", $locator);
		$locator  = str_replace("$", "_", $locator);
		$fileext  = strtolower(array_pop(explode(".", $file)));
		unset($_FILES);
	} else {
		echo $htmlhead . "<font color='#A00000'><h1>File not found</h1></font>" . $htmlfoot;
	}
} else {
	die($htmlhead . "<font color='#A00000'><h1>Internal error</h1></font>The server experiecned an internal error (Filesize over 200 MB?)<p><h2>Thank you!</h2>" . $htmlfoot);
}
// now look up in the database
$sql    = "SELECT DISTINCT $db.$dbtable.*, $db.$descrtable.descr 
          FROM $db.$dbtable LEFT JOIN $db.$descrtable ON $db.$dbtable.md5 = $db.$descrtable.md5
          WHERE $db.$dbtable.MD5='$md5'";
$result = mysql_query($sql, $con);
if (!$result) {
	die($htmlhead . "<font color='#A00000'><h1>Error</h1></font>" . mysql_error() . "<br>Cannot proceed.<p>Please, report the error from <a href=>the main page</a>." . $htmlfoot);
}
$rows = mysql_fetch_assoc($result);
//mysql_close($con);
// if book found
if (strlen($rows['MD5']) == 32) {
	$editing = true;
	@unlink("from_ext_" . $file);
	$mode             = "<font size=5 color=red>" . $LANG_MESS_116 . "</font>";
	// replace all single-quotes, they work as delimiters in HTML and SQL
	$generic          = htmlspecialchars($rows['Generic'], ENT_QUOTES);
	$title            = htmlspecialchars($rows['Title'], ENT_QUOTES);
	$filesize         = $rows['Filesize'];
	$id               = $rows['ID'];
	$fileext          = $rows['Extension'];
	$locator          = $rows['Locator'];
	$locator          = str_replace("'", " ", $locator);
	$locator          = str_replace("#", "_", $locator);
	$locator          = str_replace('\\', '/', $locator);
	$locator          = str_replace('\&', 'and', $locator);
	$author           = htmlspecialchars($rows['Author'], ENT_QUOTES);
	$topic            = htmlspecialchars($rows['Topic'], ENT_QUOTES);
	$volinfo          = htmlspecialchars($rows['VolumeInfo'], ENT_QUOTES);
	$year             = $rows['Year'];
	$publisher        = htmlspecialchars($rows['Publisher'], ENT_QUOTES);
	$city             = htmlspecialchars($rows['City'], ENT_QUOTES);
	$edition          = htmlspecialchars($rows['Edition'], ENT_QUOTES);
	$pages            = htmlspecialchars($rows['Pages'], ENT_QUOTES);
	$identifier       = htmlspecialchars($rows['Identifier'], ENT_QUOTES);
	$asin             = htmlspecialchars($rows['ASIN'], ENT_QUOTES);
	$language         = htmlspecialchars($rows['Language'], ENT_QUOTES);
	$library          = htmlspecialchars($rows['Library'], ENT_QUOTES);
	$issue            = htmlspecialchars($rows['Issue'], ENT_QUOTES);
	$orientation      = htmlspecialchars($rows['Orientation'], ENT_QUOTES);
	$dpi              = $rows['DPI'];
	$id              = $rows['ID'];
	$color            = htmlspecialchars($rows['Color'], ENT_QUOTES);
	$cleaned          = htmlspecialchars($rows['Cleaned'], ENT_QUOTES);
	$commentary       = htmlspecialchars($rows['Commentary'], ENT_QUOTES);
	$series           = htmlspecialchars($rows['Series'], ENT_QUOTES);
	$periodical       = htmlspecialchars($rows['Periodical'], ENT_QUOTES);
	$coverurl         = htmlspecialchars($rows['Coverurl'], ENT_QUOTES);
	$udc              = htmlspecialchars($rows['UDC'], ENT_QUOTES);
	$lbc              = htmlspecialchars($rows['LBC'], ENT_QUOTES);
	//$bookcode = htmlspecialchars($rows['BooksellingCode'],ENT_QUOTES);
	$description      = htmlspecialchars($rows['descr'], ENT_QUOTES);
	$issn             = htmlspecialchars($rows['ISSN'], ENT_QUOTES);
	$ddc              = htmlspecialchars($rows['DDC'], ENT_QUOTES);
	$lcc              = htmlspecialchars($rows['LCC'], ENT_QUOTES);
	$googlebookid     = htmlspecialchars($rows['Googlebookid'], ENT_QUOTES);
	$doi              = htmlspecialchars($rows['Doi'], ENT_QUOTES);
	$searchable       = htmlspecialchars($rows['Searchable'], ENT_QUOTES);
	$bookmarked       = htmlspecialchars($rows['Bookmarked'], ENT_QUOTES);
	$scanned          = htmlspecialchars($rows['Scanned'], ENT_QUOTES);
	$paginated        = htmlspecialchars($rows['Paginated'], ENT_QUOTES);
	$timelastmodified = htmlspecialchars($rows['TimeLastModified'], ENT_QUOTES);
	$timeadded        = htmlspecialchars($rows['TimeAdded'], ENT_QUOTES);
} else {
	$editing = false;
	$mode    = "<font size=5 color=green>" . $LANG_MESS_117 . "</font>";
}
if (isset($_POST['amazoncom'])) {
	$searchable = $_GET['ocr'];
	$filesize   = $_GET['filesize'];
	$fileext    = $_GET['fileext'];
	$locator    = $_GET['locator'];
	$number     = str_ireplace('Х', 'X', str_replace('--', '-', str_replace('—', '-', str_replace('‒', '-', str_replace('–', '-', str_replace(' ', '-', trimarray(htmlspecialchars($_POST['isbn'], ENT_QUOTES))))))));
	if (strlen($number) != 10) {
		require_once 'ISBN-0.1.6/ISBN.php';
		$number = ISBN::convert($number, ISBN::validate($number), ISBN_VERSION_ISBN_10);
	}
	$isbn = $number;
	include 'amazonRequestcom.php';
	$amazonInfo  = amazonInfo($isbn, $public_key, $private_key);
	$amazonError = $amazonInfo['error'];
	if ($amazonError == '') {
		$title = trimarray(htmlspecialchars($amazonInfo['Title'], ENT_QUOTES));
		if (preg_match("|([^\(]*)\((.*)\)|sei", $title, $arr)) {
			$series  = $arr[2];
			$seriesa = $arr[1];
			$title   = $seriesa;
		} else {
			$series = "";
			$title  = $title;
		}
		$author      = trimarray(htmlspecialchars($amazonInfo['Author'], ENT_QUOTES));
		$year        = trimarray(htmlspecialchars($amazonInfo['Year'], ENT_QUOTES));
		$publisher   = trimarray(htmlspecialchars($amazonInfo['Publisher'], ENT_QUOTES));
		$edition     = trimarray(htmlspecialchars($amazonInfo['Edition'], ENT_QUOTES));
		$pages       = trimarray(htmlspecialchars($amazonInfo['Pages'], ENT_QUOTES));
		$identifier  = trimarray(htmlspecialchars($amazonInfo['ISBN'], ENT_QUOTES)) . ',' . trimarray(htmlspecialchars($amazonInfo['EAN'], ENT_QUOTES));
		$asin        = trimarray(htmlspecialchars($amazonInfo['ASIN'], ENT_QUOTES));
		$language    = trimarray(htmlspecialchars($amazonInfo['Language'], ENT_QUOTES));
		//$commentary = trimarray(htmlspecialchars($amazonInfo['Content'],ENT_QUOTES)); 
		$description = trimarray(htmlspecialchars($amazonInfo['Content'], ENT_QUOTES));
		$coverurl    = trimarray(htmlspecialchars($amazonInfo['Image'], ENT_QUOTES));
	}
}
if (isset($_POST['amazonde'])) {
	$searchable = $_GET['ocr'];
	$filesize   = $_GET['filesize'];
	$fileext    = $_GET['fileext'];
	$locator    = $_GET['locator'];
	$number     = str_ireplace('Х', 'X', str_replace('--', '-', str_replace('—', '-', str_replace('‒', '-', str_replace('–', '-', str_replace(' ', '-', trimarray(htmlspecialchars($_POST['isbn'], ENT_QUOTES))))))));
	if (strlen($number) != 10) {
		require_once 'ISBN-0.1.6/ISBN.php';
		$number = ISBN::convert($number, ISBN::validate($number), ISBN_VERSION_ISBN_10);
	}
	$isbn = $number;
	include 'amazonRequestde.php';
	$amazonInfo  = amazonInfo($isbn, $public_key, $private_key);
	$amazonError = $amazonInfo['error'];
	if ($amazonError == '') {
		$title = trimarray(htmlspecialchars($amazonInfo['Title'], ENT_QUOTES));
		if (preg_match("|([^\(]*)\((.*)\)|sei", $title, $arr)) {
			$series  = $arr[2];
			$seriesa = $arr[1];
			$title   = $seriesa;
		} else {
			$series = "";
			$title  = $title;
		}
		$author      = trimarray(htmlspecialchars($amazonInfo['Author'], ENT_QUOTES));
		$year        = trimarray(htmlspecialchars($amazonInfo['Year'], ENT_QUOTES));
		$publisher   = trimarray(htmlspecialchars($amazonInfo['Publisher'], ENT_QUOTES));
		$edition     = trimarray(htmlspecialchars($amazonInfo['Edition'], ENT_QUOTES));
		$pages       = trimarray(htmlspecialchars($amazonInfo['Pages'], ENT_QUOTES));
		$identifier  = trimarray(htmlspecialchars($amazonInfo['ISBN'], ENT_QUOTES)) . ',' . trimarray(htmlspecialchars($amazonInfo['EAN'], ENT_QUOTES));
		$asin        = trimarray(htmlspecialchars($amazonInfo['ASIN'], ENT_QUOTES));
		$language    = trimarray(htmlspecialchars($amazonInfo['Language'], ENT_QUOTES));
		//$commentary = trimarray(htmlspecialchars($amazonInfo['Content'],ENT_QUOTES)); 
		$description = trimarray(htmlspecialchars($amazonInfo['Content'], ENT_QUOTES));
		$coverurl    = trimarray(htmlspecialchars($amazonInfo['Image'], ENT_QUOTES));
	}
}
if (isset($_POST['amazoncouk'])) {
	$searchable = $_GET['ocr'];
	$filesize   = $_GET['filesize'];
	$fileext    = $_GET['fileext'];
	$locator    = $_GET['locator'];
	$number     = str_ireplace('Х', 'X', str_replace('--', '-', str_replace('—', '-', str_replace('‒', '-', str_replace('–', '-', str_replace(' ', '-', trimarray(htmlspecialchars($_POST['isbn'], ENT_QUOTES))))))));
	if (strlen($number) != 10) {
		require_once 'ISBN-0.1.6/ISBN.php';
		$number = ISBN::convert($number, ISBN::validate($number), ISBN_VERSION_ISBN_10);
	}
	$isbn = $number;
	include 'amazonRequestcouk.php';
	$amazonInfo  = amazonInfo($isbn, $public_key, $private_key);
	$amazonError = $amazonInfo['error'];
	if ($amazonError == '') {
		$title = trimarray(htmlspecialchars($amazonInfo['Title'], ENT_QUOTES));
		if (preg_match("|([^\(]*)\((.*)\)|sei", $title, $arr)) {
			$series  = $arr[2];
			$seriesa = $arr[1];
			$title   = $seriesa;
		} else {
			$series = "";
			$title  = $title;
		}
		$author      = trimarray(htmlspecialchars($amazonInfo['Author'], ENT_QUOTES));
		$year        = trimarray(htmlspecialchars($amazonInfo['Year'], ENT_QUOTES));
		$publisher   = trimarray(htmlspecialchars($amazonInfo['Publisher'], ENT_QUOTES));
		$edition     = trimarray(htmlspecialchars($amazonInfo['Edition'], ENT_QUOTES));
		$pages       = trimarray(htmlspecialchars($amazonInfo['Pages'], ENT_QUOTES));
		$identifier  = trimarray(htmlspecialchars($amazonInfo['ISBN'], ENT_QUOTES)) . ',' . trimarray(htmlspecialchars($amazonInfo['EAN'], ENT_QUOTES));
		$asin        = trimarray(htmlspecialchars($amazonInfo['ASIN'], ENT_QUOTES));
		$language    = trimarray(htmlspecialchars($amazonInfo['Language'], ENT_QUOTES));
		//$commentary = trimarray(htmlspecialchars($amazonInfo['Content'],ENT_QUOTES)); 
		$description = trimarray(htmlspecialchars($amazonInfo['Content'], ENT_QUOTES));
		$coverurl    = trimarray(htmlspecialchars($amazonInfo['Image'], ENT_QUOTES));
	}
}
if (isset($_POST['amazonfr'])) {
	$searchable = $_GET['ocr'];
	$filesize   = $_GET['filesize'];
	$fileext    = $_GET['fileext'];
	$locator    = $_GET['locator'];
	$number     = str_ireplace('Х', 'X', str_replace('--', '-', str_replace('—', '-', str_replace('‒', '-', str_replace('–', '-', str_replace(' ', '-', trimarray(htmlspecialchars($_POST['isbn'], ENT_QUOTES))))))));
	if (strlen($number) != 10) {
		require_once 'ISBN-0.1.6/ISBN.php';
		$number = ISBN::convert($number, ISBN::validate($number), ISBN_VERSION_ISBN_10);
	}
	$isbn = $number;
	include 'amazonRequestfr.php';
	$amazonInfo  = amazonInfo($isbn, $public_key, $private_key);
	$amazonError = $amazonInfo['error'];
	if ($amazonError == '') {
		$title = trimarray(htmlspecialchars($amazonInfo['Title'], ENT_QUOTES));
		if (preg_match("|([^\(]*)\((.*)\)|sei", $title, $arr)) {
			$series  = $arr[2];
			$seriesa = $arr[1];
			$title   = $seriesa;
		} else {
			$series = "";
			$title  = $title;
		}
		$author      = trimarray(htmlspecialchars($amazonInfo['Author'], ENT_QUOTES));
		$year        = trimarray(htmlspecialchars($amazonInfo['Year'], ENT_QUOTES));
		$publisher   = trimarray(htmlspecialchars($amazonInfo['Publisher'], ENT_QUOTES));
		$edition     = trimarray(htmlspecialchars($amazonInfo['Edition'], ENT_QUOTES));
		$pages       = trimarray(htmlspecialchars($amazonInfo['Pages'], ENT_QUOTES));
		$identifier  = trimarray(htmlspecialchars($amazonInfo['ISBN'], ENT_QUOTES)) . ',' . trimarray(htmlspecialchars($amazonInfo['EAN'], ENT_QUOTES));
		$asin        = trimarray(htmlspecialchars($amazonInfo['ASIN'], ENT_QUOTES));
		$language    = trimarray(htmlspecialchars($amazonInfo['Language'], ENT_QUOTES));
		//$commentary = trimarray(htmlspecialchars($amazonInfo['Content'],ENT_QUOTES)); 
		$description = trimarray(htmlspecialchars($amazonInfo['Content'], ENT_QUOTES));
		$coverurl    = trimarray(htmlspecialchars($amazonInfo['Image'], ENT_QUOTES));
	}
}
if (isset($_POST['ozon'])) {
	$searchable = $_GET['ocr'];
	$filesize   = $_GET['filesize'];
	$fileext    = $_GET['fileext'];
	$locator    = $_GET['locator'];
	$locator    = str_replace("'", " ", $locator);
	$locator    = str_replace("#", "_", $locator);
	$locator    = str_replace('\\', '/', $locator);
	$number     = str_ireplace('Х', 'X', str_replace('--', '-', str_replace('—', '-', str_replace('‒', '-', str_replace('–', '-', str_replace(' ', '-', trimarray(htmlspecialchars($_POST['isbn'], ENT_QUOTES))))))));
	if (!(substr_count(trim($number), '-') == 3) && (strlen(trim($number)) == 13) || !(substr_count(trim($number), '-') == 4) && (strlen(trim($number)) == 17)) {
		require_once 'ISBN-0.1.6/ISBN.php';
		$isbn   = new ISBN($number);
		$number = substr($isbn->getISBNDisplayable(), 9);
	}
	$isbn = $number;
	include 'ozonRequest.php';
	$ozonError = $ozonInfo['error'];
	if ($ozonError == '') {
		$title       = trimarray(htmlspecialchars($ozonInfo['Title'], ENT_QUOTES));
		$author      = trimarray(htmlspecialchars($ozonInfo['Author'], ENT_QUOTES));
		$edition     = trimarray(htmlspecialchars($ozonInfo['Edition'], ENT_QUOTES));
		$series      = trimarray(htmlspecialchars($ozonInfo['Series'], ENT_QUOTES));
		$publisher   = trimarray(htmlspecialchars($ozonInfo['Publisher'], ENT_QUOTES));
		$year        = trimarray(htmlspecialchars($ozonInfo['Year'], ENT_QUOTES));
		$pages       = trimarray(htmlspecialchars($ozonInfo['Pages'], ENT_QUOTES));
		$identifier  = trimarray(htmlspecialchars($ozonInfo['ISBN'], ENT_QUOTES));
		$description = trimarray(htmlspecialchars($ozonInfo['Annotation'], ENT_QUOTES));
		// $topic = trimarray(htmlspecialchars($ozonInfo['Topic'],ENT_QUOTES)); 
		$image       = trimarray(htmlspecialchars($ozonInfo['Picture'], ENT_QUOTES));
		$image       = str_replace('small/', '', $image);
		$coverurl    = str_replace(".gif", ".jpg", $image);
		$language    = trimarray(htmlspecialchars($ozonInfo['Language'], ENT_QUOTES));
	}
}
//RGB
if (isset($_POST['rgb'])) {
	$searchable = $_GET['ocr'];
	$filesize   = $_GET['filesize'];
	$fileext    = $_GET['fileext'];
	$locator    = $_GET['locator'];
	$number     = str_ireplace('Х', 'X', str_replace('--', '-', str_replace('—', '-', str_replace('‒', '-', str_replace('–', '-', str_replace(' ', '-', trimarray(htmlspecialchars($_POST['isbn'], ENT_QUOTES))))))));
	if (!(substr_count(trim($number), '-') == 3) && (strlen(trim($number)) == 13) || !(substr_count(trim($number), '-') == 4) && (strlen(trim($number)) == 17)) {
		require_once 'ISBN-0.1.6/ISBN.php';
		$isbn   = new ISBN($number);
		$number = substr($isbn->getISBNDisplayable(), 9);
	}
	$isbn = $number;
	include 'rgbRequest.php';
	$title       = html_entity_decode(trimarray(htmlspecialchars($rsltitle, ENT_QUOTES)));
	$author      = html_entity_decode(trimarray(htmlspecialchars($rslauthor, ENT_QUOTES)));
	$city        = html_entity_decode(trimarray(htmlspecialchars($rslcity, ENT_QUOTES)));
	$publisher   = html_entity_decode(trimarray(htmlspecialchars($rslpublisher, ENT_QUOTES)));
	$year        = html_entity_decode(trimarray(htmlspecialchars($rslyear, ENT_QUOTES)));
	$edition     = html_entity_decode(trimarray(htmlspecialchars($rsledition, ENT_QUOTES)));
	$volumeninfo = html_entity_decode(trimarray(htmlspecialchars($rslvolumeninfo, ENT_QUOTES)));
	$pages       = html_entity_decode(trimarray(htmlspecialchars($rslpages, ENT_QUOTES)));
	$identifier  = html_entity_decode(trimarray(htmlspecialchars($rslidentifier, ENT_QUOTES)));
	$language    = html_entity_decode(trimarray(htmlspecialchars($rsllanguage, ENT_QUOTES)));
	//$topic = html_entity_decode(trimarray(htmlspecialchars($rsltopic,ENT_QUOTES)));
	$udc         = html_entity_decode(trimarray(htmlspecialchars($rsludc, ENT_QUOTES)));
	$series      = html_entity_decode(trimarray(htmlspecialchars($rslseries, ENT_QUOTES)));
	$description = html_entity_decode(trimarray(htmlspecialchars($rsldescription, ENT_QUOTES)));
	$language    = html_entity_decode(trimarray(htmlspecialchars($rsllanguage, ENT_QUOTES)));
}
//LOC
if (isset($_POST['loc'])) {
	$searchable = $_GET['ocr'];
	$filesize   = $_GET['filesize'];
	$fileext    = $_GET['fileext'];
	$locator    = $_GET['locator'];
	$number     = str_ireplace('Х', 'X', str_replace('--', '-', str_replace('—', '-', str_replace('‒', '-', str_replace('–', '-', str_replace(' ', '-', trimarray(htmlspecialchars($_POST['isbn'], ENT_QUOTES))))))));
	if (!(substr_count(trim($number), '-') == 3) && (strlen(trim($number)) == 13) || !(substr_count(trim($number), '-') == 4) && (strlen(trim($number)) == 17)) {
		require_once 'ISBN-0.1.6/ISBN.php';
		$isbn   = new ISBN($number);
		$number = substr($isbn->getISBNDisplayable(), 9);
	}
	$isbn = $number;
	include 'locRequest.php';
}
//NLR
if (isset($_POST['nlr'])) {
	$searchable = $_GET['ocr'];
	$filesize   = $_GET['filesize'];
	$fileext    = $_GET['fileext'];
	$locator    = $_GET['locator'];
	$number     = str_ireplace('Х', 'X', str_replace('--', '-', str_replace('—', '-', str_replace('‒', '-', str_replace('–', '-', str_replace(' ', '-', trimarray(htmlspecialchars($_POST['isbn'], ENT_QUOTES))))))));
	if (!(substr_count(trim($number), '-') == 3) && (strlen(trim($number)) == 13) || !(substr_count(trim($number), '-') == 4) && (strlen(trim($number)) == 17)) {
		require_once 'ISBN-0.1.6/ISBN.php';
		$isbn   = new ISBN($number);
		$number = substr($isbn->getISBNDisplayable(), 9);
	}
	$isbn = htmlspecialchars($number, ENT_QUOTES);
	include 'nlrRequest.php';
}
if (isset($_POST['ol'])) {
	$searchable = $_GET['ocr'];
	$filesize   = $_GET['filesize'];
	$fileext    = $_GET['fileext'];
	$locator    = $_GET['locator'];
	$olid       = str_ireplace('Х', 'X', str_replace('--', '-', str_replace('—', '-', str_replace('‒', '-', str_replace('–', '-', str_replace(' ', '-', trimarray(htmlspecialchars($_POST['isbn'], ENT_QUOTES))))))));
	$isbn       = trimarray(htmlspecialchars($olid, ENT_QUOTES));
	include 'openlibRequest.php';
	$title         = trimarray(htmlspecialchars($a1titlesall, ENT_QUOTES));
	$publisher     = trimarray(htmlspecialchars($a1publishersall, ENT_QUOTES));
	$year          = trimarray(htmlspecialchars($a1publish_date, ENT_QUOTES));
	$pages         = trimarray(htmlspecialchars($a1number_of_pages, ENT_QUOTES));
	$ddc           = trimarray(htmlspecialchars($a1dewey_decimal_classall, ENT_QUOTES));
	$lcc           = trimarray(htmlspecialchars($a1lccnall, ENT_QUOTES));
	$city          = trimarray(htmlspecialchars($a1publish_placesall, ENT_QUOTES));
	$identifier    = trimarray(htmlspecialchars($a1isbnall, ENT_QUOTES));
	$coverurl      = trimarray(htmlspecialchars($a1coversall, ENT_QUOTES));
	// $topic = trimarray(htmlspecialchars($a1subjectsall,ENT_QUOTES));    
	$author        = trimarray(htmlspecialchars($a1by_statement, ENT_QUOTES));
	$edition       = trimarray(htmlspecialchars($a1edition_name, ENT_QUOTES));
	$language      = trimarray(htmlspecialchars($a1languagesall, ENT_QUOTES));
	$openlibraryid = trimarray(htmlspecialchars($olid, ENT_QUOTES));
	$series        = trimarray(htmlspecialchars($a1seriesall, ENT_QUOTES));
}
if (isset($_POST['bg'])) {
	$searchable = $_GET['ocr'];
	$filesize   = $_GET['filesize'];
	$fileext    = $_GET['fileext'];
	$locator    = $_GET['locator'];
	$bgid       = str_ireplace('Х', 'X', str_replace('--', '-', str_replace('—', '-', str_replace('‒', '-', str_replace('–', '-', str_replace(' ', '-', trimarray(htmlspecialchars($_POST['isbn'], ENT_QUOTES))))))));
	$isbn       = trimarray(htmlspecialchars($bgid, ENT_QUOTES));
	include 'booksgoogleRequest.php';
	$title        = trimarray(htmlspecialchars($btitle, ENT_QUOTES));
	$publisher    = trimarray(htmlspecialchars($bpublisher, ENT_QUOTES));
	$year         = trimarray(htmlspecialchars($byear, ENT_QUOTES));
	$lcc          = trimarray(htmlspecialchars($blccn, ENT_QUOTES));
	$identifier   = trimarray(htmlspecialchars($bisbn, ENT_QUOTES));
	$coverurl     = trimarray(htmlspecialchars($bcover, ENT_QUOTES));
	$author       = trimarray(htmlspecialchars($bauthor, ENT_QUOTES));
	$googlebookid = trimarray(htmlspecialchars($bgid, ENT_QUOTES));
	$series       = trimarray(htmlspecialchars($bseries, ENT_QUOTES));
	$description  = trimarray(htmlspecialchars($bdescr2, ENT_QUOTES));
	$pages        = trimarray(htmlspecialchars($bpages, ENT_QUOTES));
	$edition      = trimarray(htmlspecialchars($bedit, ENT_QUOTES));
}
//получение метаданных из БД Либгена(для улучшенных версий книг)
if (isset($_POST['lg'])) {
	$searchable = $_GET['ocr'];
	$filesize   = $_GET['filesize'];
	$fileext    = $_GET['fileext'];
	$locator    = $_GET['locator'];
	$lgmd5      = trimarray(htmlspecialchars($_POST['isbn'], ENT_QUOTES));
	if (strlen($lgmd5) == '32') {
		$lgsearchid = "MD5='$lgmd5'";
	} else {
		$lgsearchid = "ID='$lgmd5'";
	}
	$sqllg        = "SELECT DISTINCT $db.$dbtable.*, $db.$descrtable.descr 
          FROM $db.$dbtable LEFT JOIN $db.$descrtable ON $db.$dbtable.md5 = $db.$descrtable.md5
          WHERE $db.$dbtable." . $lgsearchid;
	$resultlg     = mysql_query($sqllg, $con);
	$rowslg       = mysql_fetch_assoc($resultlg);
	$title        = trimarray(htmlspecialchars($rowslg['Title'], ENT_QUOTES));
	$author       = trimarray(htmlspecialchars($rowslg['Author'], ENT_QUOTES));
	$topic        = trimarray(htmlspecialchars($rowslg['Topic'], ENT_QUOTES));
	$volinfo      = trimarray(htmlspecialchars($rowslg['VolumeInfo'], ENT_QUOTES));
	$year         = trimarray(htmlspecialchars($rowslg['Year'], ENT_QUOTES));
	$publisher    = trimarray(htmlspecialchars($rowslg['Publisher'], ENT_QUOTES));
	$city         = trimarray(htmlspecialchars($rowslg['City'], ENT_QUOTES));
	$edition      = trimarray(htmlspecialchars($rowslg['Edition'], ENT_QUOTES));
	$pages        = trimarray(htmlspecialchars($rowslg['Pages'], ENT_QUOTES));
	$identifier   = trimarray(htmlspecialchars($rowslg['Identifier'], ENT_QUOTES));
	$asin         = trimarray(htmlspecialchars($rowslg['ASIN'], ENT_QUOTES));
	$language     = trimarray(htmlspecialchars($rowslg['Language'], ENT_QUOTES));
	$series       = trimarray(htmlspecialchars($rowslg['Series'], ENT_QUOTES));
	$periodical   = trimarray(htmlspecialchars($rowslg['Periodical'], ENT_QUOTES));
	$coverurl     = trimarray(htmlspecialchars($rowslg['Coverurl'], ENT_QUOTES));
	$udc          = trimarray(htmlspecialchars($rowslg['UDC'], ENT_QUOTES));
	$lbc          = trimarray(htmlspecialchars($rowslg['LBC'], ENT_QUOTES));
	$description  = trimarray(htmlspecialchars($rowslg['descr'], ENT_QUOTES));
	$issn         = trimarray(htmlspecialchars($rowslg['ISSN'], ENT_QUOTES));
	$ddc          = trimarray(htmlspecialchars($rowslg['DDC'], ENT_QUOTES));
	$lcc          = trimarray(htmlspecialchars($rowslg['LCC'], ENT_QUOTES));
	$googlebookid = trimarray(htmlspecialchars($rowslg['Googlebookid'], ENT_QUOTES));
	$doi          = trimarray(htmlspecialchars($rowslg['Doi'], ENT_QUOTES));
}
// получение метаданых из файла (с пом. calibre)
if (isset($_POST['gm'])) {
	$searchable = $_GET['ocr'];
	$filesize   = $_GET['filesize'];
	$fileext    = $_GET['fileext'];
	$locator    = $_GET['locator'];
	include 'calibregetmetadata.php';
	$title        = trimarray(htmlspecialchars($gmtitle, ENT_QUOTES));
	$publisher    = trimarray(htmlspecialchars($gmpublisher, ENT_QUOTES));
	$year         = trimarray(htmlspecialchars($gmyear, ENT_QUOTES));
	$identifier   = trimarray(htmlspecialchars($gmisbn, ENT_QUOTES));
	$author       = trimarray(htmlspecialchars($gmauthor, ENT_QUOTES));
	$googlebookid = trimarray(htmlspecialchars($gmgbid, ENT_QUOTES));
	$series       = trimarray(htmlspecialchars($gmseries, ENT_QUOTES));
	$language     = trimarray(htmlspecialchars($gmlang, ENT_QUOTES));
	$commentary   = trimarray(htmlspecialchars($gmcomment, ENT_QUOTES));
	$description  = trimarray(htmlspecialchars($gmdescr, ENT_QUOTES));
	$coverurl     = trimarray(htmlspecialchars($gmcovers, ENT_QUOTES));
}
// получение метаданых c Worldcat
if (isset($_POST['wc'])) {
	$searchable = $_GET['ocr'];
	$filesize   = $_GET['filesize'];
	$fileext    = $_GET['fileext'];
	$locator    = $_GET['locator'];
	$oclc       = str_ireplace('Х', 'X', str_replace('--', '-', str_replace('—', '-', str_replace('‒', '-', str_replace('–', '-', str_replace(' ', '-', trimarray(htmlspecialchars($_POST['isbn'], ENT_QUOTES))))))));
	include 'wordcatrequest.php';
	$title       = trimarray(htmlspecialchars($wctitle, ENT_QUOTES));
	$series      = trimarray(htmlspecialchars($wcseries, ENT_QUOTES));
	$publisher   = trimarray(htmlspecialchars($wcpubl, ENT_QUOTES));
	$year        = trimarray(htmlspecialchars($wcyear, ENT_QUOTES));
	$identifier  = trimarray(htmlspecialchars($wcisbn, ENT_QUOTES));
	$author      = trimarray(htmlspecialchars($wcauth, ENT_QUOTES));
	$edition     = trimarray(htmlspecialchars($wcedit, ENT_QUOTES));
	$language    = trimarray(htmlspecialchars($wclang, ENT_QUOTES));
	$description = trimarray(htmlspecialchars($wcdescr, ENT_QUOTES));
	$city        = trimarray(htmlspecialchars($wccity, ENT_QUOTES));
	$pages       = trimarray(htmlspecialchars($wcpage, ENT_QUOTES));
	$coverurl    = trimarray(htmlspecialchars($wccover, ENT_QUOTES));
	//$topic = trimarray(htmlspecialchars($wctopic,ENT_QUOTES));
}
//echo $isbnfind[0][1];
//print_r($isbnfind);
//if(!isset($_POST['isbn'])){$identifier = $isbnfind[0][1];}
//else{$identifier = $_POST['isbn'];}
$isbnForm = "<form action='registration.php?md5=" . $md5 . "&filesize=" . $filesize . "&fileext=" . $fileext . "&locator=" . $locator . "&ocr=" . $searchable . "' method='post' >" . $mode . "<br>" . $LANG_MESS_109 . " <input type='text' name='isbn' size='20' maxlength='80' value='" . htmlspecialchars(@$_POST['isbn'], ENT_QUOTES) . "' /> [ " . $LANG_MESS_107 . "<SELECT NAME='ISBNFind' ONCHANGE='form.isbn.value=this.options[this.selectedIndex].value'><OPTION></OPTION>" . $isbnfind . "</SELECT> ] search in:

<table border=0><tr><td valign = 'top'><input type='submit' value='Amazon.com' name='amazoncom'/>
<input type='submit' value='.de' name='amazonde'/>
<input type='submit' value='.fr' name='amazonfr'/>
<input type='submit' value='.co.uk' name='amazoncouk'/><br><font size = 1>ISBN, ASIN</font></td>
<td valign = 'top'><input type='submit' value='Books.google.com' name='bg'/><br><font size = 1>Google Books ID, <br>" . $LANG_MESS_111 . ":S98DAAAAMBAJ</font></td>
<td valign = 'top'><input type='submit' value='Ozon.ru&nbsp;&nbsp;&nbsp;' name='ozon'/>&nbsp;<input type='submit' value='Loc.gov' name='loc'/><br><font size = 1>ISBN " . $LANG_MESS_115 . " '-'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ISBN with '-'</font></td>
<td valign = 'top'><input type='submit' value='" . $LANG_MESS_113 . "' name='rgb'/><br><font size = 1>ISBN " . $LANG_MESS_115 . " '-'</font></td>
<td valign = 'top'><input type='submit' value='WorldCat.org' name='wc'/><br><font size = 1>OCLC or ISBN or " . $LANG_MESS_6 . " + " . $LANG_MESS_5 . "</font></td>
</tr><tr>
<td valign = 'top'><input type='submit' value='" . $LANG_MESS_114 . "' name='lg'/><br><font size = 1>MD5 hash or ID from LibGen DB</font></td>
<td valign = 'top'><input type='submit' value='&nbsp;&nbsp;OpenLibrary.org&nbsp;&nbsp;&nbsp;' name='ol'/><br><font size = 1>OpenLibrary ID, <br>" . $LANG_MESS_111 . ":OL13502089M</font></td>
<td valign = 'top'><input type='submit' value='Get metadata from file' name='gm'/></td>
<td valign = 'top'><input type='submit' value='" . $LANG_MESS_112 . "' name='nlr'/><br><font size = 1>ISBN " . $LANG_MESS_115 . " '-'</font></td>
<td valign = 'top'></td>
</tr></table>


            " . @$amazonError . @$ozonError . @$rgbError . @$locError . @$nlrError . @$olError . @$wcerror . "</form>";
$regform  = $isbnForm . "<form action='register.php' method='post'>
<table width=1000 cellspacing=0 cellpadding=0 border=0 align=center>

<tr><td width=20%><font color=gray><b>" . $LANG_MESS_92 . "</b></font></td><td colspan=3><input readonly type='text' name='Locator' size=146 value='" . $locator . "' maxlength=260/></td></tr>
<tr><td width=20%><b>" . $LANG_MESS_5 . "</b></td><td colspan=3><input type='text' name='Title' id='2' size=116 value='" . $title . "' maxlength=2000/><b>" . $LANG_MESS_42 . "</b><input type='text' name='VolumeInfo' size=15 value='" . $volinfo . "' maxlength=100/></td></tr>
<tr><td width=20%><b>" . $LANG_MESS_6 . "</b></td><td colspan=3><input type='text' name='Author' id='1' size=146 value='" . $author . "' maxlength=1000/></td></tr>
<tr><td width=20%><b>" . $LANG_MESS_7 . "+№</b></td><td><input type='text' name='Series' size=56 value='" . $series . "' maxlength=300/></td><td width=20%><b>" . $LANG_MESS_8 . "+№</b></td><td><input type='text' name='Periodical' size=56 value='" . $periodical . "' maxlength=200/></td></tr>
<tr><td width=20%><b>" . $LANG_MESS_9 . "</b></td><td><input type='text' name='Publisher' size=56 value='" . $publisher . "' maxlength=400/></td><td  width=20%><b>" . $LANG_MESS_93 . "</b></td><td><input type='text' name='City' size=56 value='" . $city . "' maxlength=100/></td></tr>

<tr><td width=20%><b>" . $LANG_MESS_10 . "</b></td><td><input type='text' name='Year' size=56 value='" . $year . "' maxlength=14/></td><td width=20%><b>" . $LANG_MESS_43 . "</b></td><td><input type='text' name='Edition' size=56 value='" . $edition . "' maxlength=60/></td></tr>

<tr>
<td width=20%><b>" . $LANG_MESS_11 . "</b></td>
<td><input type='text' name='Language' size=31 value='" . $language . "' maxlength=50/><select size=1 name='langselect' ONCHANGE='form.Language.value=this.options[this.selectedIndex].value'>
<OPTION></OPTION>
<OPTION VALUE='English'>English</OPTION>
<OPTION VALUE='Russian'>Russian</OPTION>
<OPTION VALUE='Ukrainian'>Ukrainian</OPTION>
<OPTION VALUE='German'>German</OPTION>
<OPTION VALUE='French'>French</OPTION>
<OPTION VALUE='Italian'>Italian</OPTION>
<OPTION VALUE='Japanese'>Japanese</OPTION>
<OPTION VALUE='Spanish'>Spanish</OPTION>
<OPTION VALUE='Portuguese'>Portuguese</OPTION>
<OPTION VALUE='Latin'>Latin</OPTION>
<OPTION VALUE='Czech'>Czech</OPTION>
<OPTION VALUE='Bulgarian '>Bulgarian </OPTION>
<OPTION VALUE='Russian (Old)'>Russian (Old)</OPTION>
<OPTION VALUE=''></OPTION>
<OPTION VALUE='Abkhaz'>Abkhaz</OPTION>
<OPTION VALUE='Afar'>Afar</OPTION>
<OPTION VALUE='Afrikaans'>Afrikaans</OPTION>
<OPTION VALUE='Akan'>Akan</OPTION>
<OPTION VALUE='Albanian'>Albanian</OPTION>
<OPTION VALUE='Amharic'>Amharic</OPTION>
<OPTION VALUE='Arabic'>Arabic</OPTION>
<OPTION VALUE='Aragonese'>Aragonese</OPTION>
<OPTION VALUE='Armenian'>Armenian</OPTION>
<OPTION VALUE='Assamese'>Assamese</OPTION>
<OPTION VALUE='Avaric'>Avaric</OPTION>
<OPTION VALUE='Avestan'>Avestan</OPTION>
<OPTION VALUE='Aymara'>Aymara</OPTION>
<OPTION VALUE='Azerbaijani'>Azerbaijani</OPTION>
<OPTION VALUE='Bambara'>Bambara</OPTION>
<OPTION VALUE='Bashkir'>Bashkir</OPTION>
<OPTION VALUE='Basque'>Basque</OPTION>
<OPTION VALUE='Belarusian'>Belarusian</OPTION>
<OPTION VALUE='Bengali'>Bengali</OPTION>
<OPTION VALUE='Bihari'>Bihari</OPTION>
<OPTION VALUE='Bislama'>Bislama</OPTION>
<OPTION VALUE='Bosnian'>Bosnian</OPTION>
<OPTION VALUE='Breton'>Breton</OPTION>
<OPTION VALUE='Burmese'>Burmese</OPTION>
<OPTION VALUE='Catalan'>Catalan</OPTION>
<OPTION VALUE='Chamorro'>Chamorro</OPTION>
<OPTION VALUE='Chechen'>Chechen</OPTION>
<OPTION VALUE='Chichewa'>Chichewa</OPTION>
<OPTION VALUE='Chinese'>Chinese</OPTION>
<OPTION VALUE='Chuvash'>Chuvash</OPTION>
<OPTION VALUE='Cornish'>Cornish</OPTION>
<OPTION VALUE='Corsican'>Corsican</OPTION>
<OPTION VALUE='Cree'>Cree</OPTION>
<OPTION VALUE='Croatian'>Croatian</OPTION>
<OPTION VALUE='Danish'>Danish</OPTION>
<OPTION VALUE='Divehi'>Divehi</OPTION>
<OPTION VALUE='Dutch'>Dutch</OPTION>
<OPTION VALUE='Dzongkha'>Dzongkha</OPTION>
<OPTION VALUE='Esperanto'>Esperanto</OPTION>
<OPTION VALUE='Estonian'>Estonian</OPTION>
<OPTION VALUE='Ewe'>Ewe</OPTION>
<OPTION VALUE='Faroese'>Faroese</OPTION>
<OPTION VALUE='Fijian'>Fijian</OPTION>
<OPTION VALUE='Finnish'>Finnish</OPTION>
<OPTION VALUE='Fula'>Fula</OPTION>
<OPTION VALUE='Galician'>Galician</OPTION>
<OPTION VALUE='Georgian'>Georgian</OPTION>
<OPTION VALUE='Greek'>Greek</OPTION>
<OPTION VALUE='Guaraní'>Guaraní</OPTION>
<OPTION VALUE='Gujarati'>Gujarati</OPTION>
<OPTION VALUE='Haitian'>Haitian</OPTION>
<OPTION VALUE='Hausa'>Hausa</OPTION>
<OPTION VALUE='Hebrew'>Hebrew</OPTION>
<OPTION VALUE='Herero'>Herero</OPTION>
<OPTION VALUE='Hindi'>Hindi</OPTION>
<OPTION VALUE='Hiri Motu'>Hiri Motu</OPTION>
<OPTION VALUE='Hungarian'>Hungarian</OPTION>
<OPTION VALUE='Interlingua'>Interlingua</OPTION>
<OPTION VALUE='Indonesian'>Indonesian</OPTION>
<OPTION VALUE='Interlingue'>Interlingue</OPTION>
<OPTION VALUE='Irish'>Irish</OPTION>
<OPTION VALUE='Igbo'>Igbo</OPTION>
<OPTION VALUE='Inupiaq'>Inupiaq</OPTION>
<OPTION VALUE='Ido'>Ido</OPTION>
<OPTION VALUE='Icelandic'>Icelandic</OPTION>
<OPTION VALUE='Inuktitut'>Inuktitut</OPTION>
<OPTION VALUE='Javanese'>Javanese</OPTION>
<OPTION VALUE='Kalaallisut'>Kalaallisut</OPTION>
<OPTION VALUE='Kannada'>Kannada</OPTION>
<OPTION VALUE='Kanuri'>Kanuri</OPTION>
<OPTION VALUE='Kashmiri'>Kashmiri</OPTION>
<OPTION VALUE='Kazakh'>Kazakh</OPTION>
<OPTION VALUE='Khmer'>Khmer</OPTION>
<OPTION VALUE='Kikuyu'>Kikuyu</OPTION>
<OPTION VALUE='Kinyarwanda'>Kinyarwanda</OPTION>
<OPTION VALUE='Kyrgyz'>Kyrgyz</OPTION>
<OPTION VALUE='Komi'>Komi</OPTION>
<OPTION VALUE='Kongo'>Kongo</OPTION>
<OPTION VALUE='Korean'>Korean</OPTION>
<OPTION VALUE='Kurdish'>Kurdish</OPTION>
<OPTION VALUE='Kwanyama'>Kwanyama</OPTION>
<OPTION VALUE='Luxembourgish'>Luxembourgish</OPTION>
<OPTION VALUE='Ganda'>Ganda</OPTION>
<OPTION VALUE='Limburgish'>Limburgish</OPTION>
<OPTION VALUE='Lingala'>Lingala</OPTION>
<OPTION VALUE='Lao'>Lao</OPTION>
<OPTION VALUE='Lithuanian'>Lithuanian</OPTION>
<OPTION VALUE='Luba-Katanga'>Luba-Katanga</OPTION>
<OPTION VALUE='Latvian'>Latvian</OPTION>
<OPTION VALUE='Manx'>Manx</OPTION>
<OPTION VALUE='Macedonian'>Macedonian</OPTION>
<OPTION VALUE='Malagasy'>Malagasy</OPTION>
<OPTION VALUE='Malay'>Malay</OPTION>
<OPTION VALUE='Malayalam'>Malayalam</OPTION>
<OPTION VALUE='Maltese'>Maltese</OPTION>
<OPTION VALUE='Māori'>Māori</OPTION>
<OPTION VALUE='Marathi'>Marathi</OPTION>
<OPTION VALUE='Marshallese'>Marshallese</OPTION>
<OPTION VALUE='Mongolian'>Mongolian</OPTION>
<OPTION VALUE='Nauru'>Nauru</OPTION>
<OPTION VALUE='Navajo'>Navajo</OPTION>
<OPTION VALUE='Norwegian Bokmål'>Norwegian Bokmål</OPTION>
<OPTION VALUE='North Ndebele'>North Ndebele</OPTION>
<OPTION VALUE='Nepali'>Nepali</OPTION>
<OPTION VALUE='Ndonga'>Ndonga</OPTION>
<OPTION VALUE='Norwegian Nynorsk'>Norwegian Nynorsk</OPTION>
<OPTION VALUE='Norwegian'>Norwegian</OPTION>
<OPTION VALUE='Nuosu'>Nuosu</OPTION>
<OPTION VALUE='South Ndebele'>South Ndebele</OPTION>
<OPTION VALUE='Occitan'>Occitan</OPTION>
<OPTION VALUE='Ojibwe'>Ojibwe</OPTION>
<OPTION VALUE='Old Church Slavonic'>Old Church Slavonic</OPTION>
<OPTION VALUE='Oromo'>Oromo</OPTION>
<OPTION VALUE='Oriya'>Oriya</OPTION>
<OPTION VALUE='Ossetian'>Ossetian</OPTION>
<OPTION VALUE='Panjabi'>Panjabi</OPTION>
<OPTION VALUE='Pāli'>Pāli</OPTION>
<OPTION VALUE='Persian'>Persian</OPTION>
<OPTION VALUE='Polish'>Polish</OPTION>
<OPTION VALUE='Pashto'>Pashto</OPTION>
<OPTION VALUE='Portuguese'>Portuguese</OPTION>
<OPTION VALUE='Quechua'>Quechua</OPTION>
<OPTION VALUE='Romansh'>Romansh</OPTION>
<OPTION VALUE='Kirundi'>Kirundi</OPTION>
<OPTION VALUE='Romanian'>Romanian</OPTION>
<OPTION VALUE='Sanskrit'>Sanskrit</OPTION>
<OPTION VALUE='Sardinian'>Sardinian</OPTION>
<OPTION VALUE='Sindhi'>Sindhi</OPTION>
<OPTION VALUE='Northern Sami'>Northern Sami</OPTION>
<OPTION VALUE='Samoan'>Samoan</OPTION>
<OPTION VALUE='Sango'>Sango</OPTION>
<OPTION VALUE='Serbian'>Serbian</OPTION>
<OPTION VALUE='Scottish Gaelic'>Scottish Gaelic</OPTION>
<OPTION VALUE='Shona'>Shona</OPTION>
<OPTION VALUE='Sinhala'>Sinhala</OPTION>
<OPTION VALUE='Slovak'>Slovak</OPTION>
<OPTION VALUE='Slovene'>Slovene</OPTION>
<OPTION VALUE='Somali'>Somali</OPTION>
<OPTION VALUE='Southern Sotho'>Southern Sotho</OPTION>
<OPTION VALUE='Sundanese'>Sundanese</OPTION>
<OPTION VALUE='Swahili'>Swahili</OPTION>
<OPTION VALUE='Swati'>Swati</OPTION>
<OPTION VALUE='Swedish'>Swedish</OPTION>
<OPTION VALUE='Tamil'>Tamil</OPTION>
<OPTION VALUE='Telugu'>Telugu</OPTION>
<OPTION VALUE='Tajik'>Tajik</OPTION>
<OPTION VALUE='Thai'>Thai</OPTION>
<OPTION VALUE='Tigrinya'>Tigrinya</OPTION>
<OPTION VALUE='Tibetan Standard'>Tibetan Standard</OPTION>
<OPTION VALUE='Turkmen'>Turkmen</OPTION>
<OPTION VALUE='Tagalog'>Tagalog</OPTION>
<OPTION VALUE='Tswana'>Tswana</OPTION>
<OPTION VALUE='Tonga'>Tonga</OPTION>
<OPTION VALUE='Turkish'>Turkish</OPTION>
<OPTION VALUE='Tsonga'>Tsonga</OPTION>
<OPTION VALUE='Tatar'>Tatar</OPTION>
<OPTION VALUE='Twi'>Twi</OPTION>
<OPTION VALUE='Tahitian'>Tahitian</OPTION>
<OPTION VALUE='Uighur'>Uighur</OPTION>
<OPTION VALUE='Urdu'>Urdu</OPTION>
<OPTION VALUE='Uzbek'>Uzbek</OPTION>
<OPTION VALUE='Venda'>Venda</OPTION>
<OPTION VALUE='Vietnamese'>Vietnamese</OPTION>
<OPTION VALUE='Volapük'>Volapük</OPTION>
<OPTION VALUE='Walloon'>Walloon</OPTION>
<OPTION VALUE='Welsh'>Welsh</OPTION>
<OPTION VALUE='Wolof'>Wolof</OPTION>
<OPTION VALUE='Western Frisian'>Western Frisian</OPTION>
<OPTION VALUE='Xhosa'>Xhosa</OPTION>
<OPTION VALUE='Yiddish'>Yiddish</OPTION>
<OPTION VALUE='Yoruba'>Yoruba</OPTION>
<OPTION VALUE='Zhuang'>Zhuang</OPTION>
<OPTION VALUE='Zulu'>Zulu</OPTION>
</SELECT></td>
<td width=20%><b>" . $LANG_MESS_61 . "</b><td><input type='text' name='Pages' size=56 value='" . $pages . "' maxlength=100/></td></tr>
<tr><td width=20%><b>ISBN</b></td><td><input type='text' name='Identifier' size=56 value='" . $identifier . "' maxlength=600/></td><td width=20%><b>ASIN</b></td><td><input type='text' name='ASIN' size=56 value='" . $asin . "' maxlength=100/></td></tr>
<tr><td width=20%><b>" . $LANG_MESS_50 . "</b></td><td><input type='text' name='Commentary' size=56 value='" . $commentary . "' maxlength=20000/></td><td width=20%><b>" . $LANG_MESS_101 . "</b></td><td><input type='text' name='Coverurl' size=56 value='" . $coverurl . "' maxlength=200/></td></tr>
<tr><td width=20%><b>" . $LANG_MESS_13 . "</b></td><td colspan=3><SELECT style='width: 820px;' NAME='TopicSelect' ONCHANGE='form.Topic.value=this.options[this.selectedIndex].value'>" . $LANG_MESS_133 . "</SELECT>>><input type='text'  readonly  name='Topic' size=5 value='" . $topic . "' maxlength=3/>
</td><tr>
<tr><td width=20%><b>" . $LANG_MESS_51 . "</b></td><td colspan=3>
<table width=100%>
<tr>
<td><b>ISSN</b></td>
<td><b>" . $LANG_MESS_100 . "</b></td>
<td><b>" . $LANG_MESS_99 . "</b></td>
<td><b>LCC</b></td>
<td><b>DDC</b></td>
<td><b>DOI</b></td>
<td><b>OpenLibrary ID</b></td>
<td><b>GoogleBook ID</b></td>
</tr>
<tr>
<td><input type='text' name='ISSN' size=12 value='" . $issn . "' maxlength=9/></td>
<td><input type='text' name='UDC' size=12 value='" . $udc . "' maxlength=200/></td>
<td><input type='text' name='LBC' size=12 value='" . $lbc . "' maxlength=200/></td>
<td><input type='text' name='LCC' size=12 value='" . $lcc . "' maxlength=50/></td>
<td><input type='text' name='DDC' size=12 value='" . $ddc . "' maxlength=45/></td>
<td><input type='text' name='Doi' size=12 value='" . $doi . "' maxlength=45/></td>
<td><input type='text' name='OpenLibraryID' size=12 value='" . $openlibraryid . "' maxlength=100/></td>
<td><input type='text' name='Googlebookid' size=12 value='" . $googlebookid . "' maxlength=45/></td>
</tr>
</table>
</td></tr>



<tr><td width=20%><b>" . $LANG_MESS_52 . "</b></td><td colspan=3>
<table width=100%>
<tr>
<td><b>DPI</b></td>
<td><b>OCR</b></td>
<td><b>" . $LANG_MESS_95 . "</b></td>
<td><b>" . $LANG_MESS_96 . "</b></td>
<td><b>" . $LANG_MESS_94 . "</b></td>
<td><b>Paginated</b></td>
<td><b>" . $LANG_MESS_98 . "</b></td>
<td><b>" . $LANG_MESS_97 . "</b></td>
</tr>
<tr>
<td><input type='text' name='DPI' size=3 value='" . $dpi . "' maxlength=6/>
<SELECT NAME='DPISelect' ONCHANGE='form.DPI.value=this.options[this.selectedIndex].value'>
<OPTION></OPTION>
<OPTION VALUE='72'>72</OPTION>
<OPTION VALUE='100'>100</OPTION>
<OPTION VALUE='150'>150</OPTION>
<OPTION VALUE='200'>200</OPTION>
<OPTION VALUE='300'>300</OPTION>
<OPTION VALUE='400'>400</OPTION>
<OPTION VALUE='450'>450</OPTION>
<OPTION VALUE='600'>600</OPTION>
<OPTION VALUE='800'>800</OPTION>
<OPTION VALUE='1000'>1000</OPTION>
<OPTION VALUE='1200'>1200</OPTION>
<OPTION VALUE='1600'>1600</OPTION>
</SELECT></td>
<td><input type='text' name='Searchable' size=3 value='" . $searchable . "' maxlength=1/>
<SELECT NAME='SearchableSelect' ONCHANGE='form.Searchable.value=this.options[this.selectedIndex].value'>
<OPTION></OPTION>
<OPTION VALUE='0'>No</OPTION>
<OPTION VALUE='1'>Yes</OPTION>
</SELECT></td>
<td><input type='text' name='Bookmarked' size=3 value='" . $bookmarked . "' maxlength=1/>
<SELECT NAME='BookmarkedSelect' ONCHANGE='form.Bookmarked.value=this.options[this.selectedIndex].value'>
<OPTION></OPTION>
<OPTION VALUE='0'>No</OPTION>
<OPTION VALUE='1'>Yes</OPTION>
</SELECT></td>
<td><input type='text' name='Scanned' size=3 value='" . $scanned . "' maxlength=1/>
<SELECT NAME='ScannedSelect' ONCHANGE='form.Scanned.value=this.options[this.selectedIndex].value'>
<OPTION></OPTION>
<OPTION VALUE='0'>No</OPTION>
<OPTION VALUE='1'>Yes</OPTION>
</SELECT></td>
<td><input type='text' name='Orientation' size=3 value='" . $orientation . "' maxlength=1/>
<SELECT NAME='OrientationSelect' ONCHANGE='form.Orientation.value=this.options[this.selectedIndex].value'>
<OPTION></OPTION>
<OPTION VALUE='0'>Portr</OPTION>
<OPTION VALUE='1'>Lands</OPTION>
</SELECT></td>
<td><input type='text' name='Paginated' size=3 value='" . $paginated . "' maxlength=1/>
<SELECT NAME='PaginatedSelect' ONCHANGE='form.Paginated.value=this.options[this.selectedIndex].value'>
<OPTION></OPTION>
<OPTION VALUE='0'>No</OPTION>
<OPTION VALUE='1'>Yes</OPTION>
</SELECT></td>
<td><input type='text' name='Color' size=3 value='" . $color . "' maxlength=1/>
<SELECT NAME='ColorSelect' ONCHANGE='form.Color.value=this.options[this.selectedIndex].value'>
<OPTION></OPTION>
<OPTION VALUE='0'>No</OPTION>
<OPTION VALUE='1'>Yes</OPTION>
</SELECT></td>
<td><input type='text' name='Cleaned' size=3 value='" . $cleaned . "' maxlength=1/>
<SELECT NAME='CleanedSelect' ONCHANGE='form.Cleaned.value=this.options[this.selectedIndex].value'>
<OPTION></OPTION>
<OPTION VALUE='0'>No</OPTION>
<OPTION VALUE='1'>Yes</OPTION>
</SELECT></td>
</tr>
</table>
</td></tr>
<tr><td width=20%><b>" . $LANG_MESS_103 . "</b></td><td colspan=3><textarea name='Description' rows=5 cols=110>" . @$description . "</textarea></td></tr>
<tr><td width=20%><b>" . $LANG_MESS_46 . "</b></td><td><input type='text' name='Library' size=56 value='" . $library . "' maxlength=50/></td><td width=20%><b>" . $LANG_MESS_47 . "</b></td><td><input type='text' name='Issue' size=56 value='" . $issue . "' maxlength=100/></td></tr>
<tr><td width=20%><font color=red><b>" . $LANG_MESS_102 . "</b></font></td><td><input type='text' name='Generic' size=56 value='" . $generic . "' maxlength=32/></td><td width=20%><font color=gray><b>MD5</b></font></td><td><input readonly type='text' name='MD5' size=44 value='" . $md5 . "' maxlength=32/><b>ID</b><input readonly type='text' name='ID' size=4 value='" . $id . "' maxlength=10/></td><tr>
<tr><td width=20%><font color=gray><b>" . $LANG_MESS_26 . "</b></font></td><td><input readonly type='text' name='Filesize' size=56 value='" . $filesize . "' maxlength=20/></td><td width=20%><font color=gray><b>" . $LANG_MESS_12 . "</b></font></td><td><input readonly type='text' name='Extension' size=56 value='" . $fileext . "' maxlength=30/></td></tr>
<tr><td width=20%><font color=gray><b>" . $LANG_MESS_44 . "</b></font></td><td><input readonly type='text' name='TimeAdded' size=56 value='" . $timeadded . "' maxlength=40/></td><td width=20%><font color=gray><b>" . $LANG_MESS_45 . "</b></font></td><td><input readonly type='text' name='TimeLastModified' size=56 value='" . $timelastmodified . "' maxlength=40/></td></tr>
<tr><td colspan=4 align='center'><input type='submit' value='" . $LANG_MESS_104 . "'/></td>
</table>

<input type='hidden' name='Edit' value='" . $editing . "'/>
</form>" . $htmlfoot;
// add new record, edit if already exists
if ($_POST['Form'] == 1) {
	if ($editing) {
		echo $regform;
		unlink($md5);
	} else {
		// save from the cache to the temporary directory (otherwise it might be automatically wiped);
		// to be copied to the repository in case of successful database registration (follows after this step)
		$tmp = str_replace('\\', '/', getcwd() . '/' . $tmpdir);
		@mkdir($tmp, 0777, true);
		$saveto = "{$tmp}/{$md5}";
		if (copy($md5, $saveto)) {
			echo $regform;
			unlink($md5);
		} else {
			echo $htmlhead . "<font color='#A00000'><h1>Upload failed</h1></font>There was an error uploading the file. Please try again!" . $htmlfoot;
		}
	}
}
// edit, if MD5 found
if ($_POST['Form'] == 2) {
	if ($editing || isset($_POST['amazoncom']) || isset($_POST['amazonde']) || isset($_POST['amazonfr']) || isset($_POST['amazoncouk']) || isset($_POST['ozon']) || isset($_POST['rgb']) || isset($_POST['nlr']) || isset($_POST['loc']) || isset($_POST['ol']) || isset($_POST['bg']) || isset($_POST['gm']) || isset($_POST['lg']) || isset($_POST['wc']))
		echo $regform;
	else
		echo $htmlhead . "<font color='#A00000'><h1>Book not found</h1></font>There is no such book in the database.<br>You are welcome to upload this piece!<p><a href='registration.php'>Go back to the upload page</a><p><h2>Thank you!</h2>" . $htmlfoot;
}
if ($_POST['Form'] == 3) {
	if ($editing) {
		echo $regform;
	} else {
		// save from the cache to the temporary directory (otherwise it might be automatically wiped);
		// to be copied to the repository in case of successful database registration (follows after this step)
		$tmp = str_replace('\\', '/', getcwd() . '/' . $tmpdir);
		@mkdir($tmp, 0777, true);
		$saveto = "{$tmp}/{$md5}";
		if (copy($uploadfile, $saveto)) {
			echo $regform;
			unlink($uploadfile);
		} else {
			echo $htmlhead . "<font color='#A00000'><h1>Upload failed</h1></font>There was an error uploading the file. Please try again!" . $htmlfoot;
		}
	}
}
@unlink('temp.txt');
unset($_FILES);
ob_flush();
?>