<?php
ini_set("memory_limit","4M");
	include 'connect.php';
	include 'html.php';
        set_time_limit(3600);

ini_set('display_errors',0);
//error_reporting(E_ALL);



function trimarray($trimstrings)
{$trimstrings = preg_replace('~[\;|\:|\,|\\\|\/|\@|\$|\-|\ |\]|\>|\}|\|]{0,5}$~isU', '', $trimstrings);
$trimstrings = preg_replace('~^[\;|\:|\,|\\\|\/|\@|\$|\-|\ |\.|\[|\<|\{|\|]{0,5}~isU', '', $trimstrings);
return $trimstrings;}



	// LOCK DB
	mysql_query('LOCK TABLE '.$dbtable.', '.$descrtable.' WRITE');

	// compute into which folder the file should be dispatched
	$id = mysql_next_id($dbtable);
	$id_descr = mysql_next_id($descrtable);
	$relativeID = $id % $modulobase;

	$savedir = $id - $relativeID;

	$relPath = $savedir.'/'.$_POST['MD5'];
	$repository = str_replace('\\','/',realpath($repository));

	// check correctness of numerical values (they cannot be equal to empty strings)
	// and some textual
	$year = trimarray($_POST['Year']);
	$pages = trimarray($_POST['Pages']);
	$issue = trimarray($_POST['Issue']);
	$dpi = trimarray($_POST['DPI']);
	$identifier = trimarray($_POST['Identifier']);

	if ($year == '') $year = 0;
	if ($pages == '') $pages = 0;
	if ($issue == '') $issue = 0;
	if ($dpi == '') $dpi = 0;
	if ($identifier == 'ISBN ') $identifier = '';

	// escape single quotes

	// check for proper MD5
	$generic = clean('Generic');
	if ($generic == '' || $generic == trimarray($_POST['MD5'])) {
		$generic = '';
	} elseif (!preg_match('/^[A-Fa-f0-9]{32}$/',$generic)) {
		echo $htmlhead."<font color='#A00000'><h1>Wrong Hash Value</h1></font>The MD5 of a better book provided is not a valid MD5.<br>You can modify it later by using MD5 as the key.".$htmlfoot;
		$generic = '';
	}

	$topic = clean('Topic');
	$title = clean('Title');
	$asin = clean('ASIN');
	$author = clean('Author');
	$volinfo = clean('VolumeInfo');
	$publisher = clean('Publisher');
        $city = clean('City');
	$edition = clean('Edition');
	$identifier = clean('Identifier');
	$language = clean('Language');
	$library = clean('Library');
	$orientation = clean('Orientation');
	$color = clean('Color');
	$cleaned = clean('Cleaned');
	$commentary = clean('Commentary');
	$series = clean('Series');
        $periodical = clean('Periodical');
        $coverurl = clean('Coverurl');
	$udc = clean('UDC');
	$lbc = clean('LBC');
        $descr = clean('Description');
        $openlibraryid = clean('OpenLibraryID');
	$issn = clean('ISSN');
	$ddc = clean('DDC');
        $lcc = clean('LCC');
	$googlebookid = clean('Googlebookid');
	$doi = clean('Doi');
        $searchable = clean('Searchable');
        $bookmarked = clean('Bookmarked');
        $scanned = clean('Scanned');
        $paginated = clean('Paginated');




    
	// open file read-only and lock before SQL-query
	if (!$_POST['Edit']){
		$tmp=str_replace('\\','/',getcwd().'/'.$tmpdir);
		$file = $tmp.'/'.trimarray($_POST['MD5']);
		@$h = fopen($file,'r');
		if (!$h) die("Cannot open temporary file '".$file."'");

		if (!flock($h,LOCK_EX)) die("<p>Cannot lock temporary file '".$file."'");

$sql1="INSERT INTO `".$dbtable."` 
(`TimeAdded`,
`ID`,
`Topic`,
`Author`,
`Title`,
`VolumeInfo`,
`Year`,
`Publisher`,
`City`,
`Edition`,
`Identifier`,
`ASIN`,
`Pages`,
`Filesize`,
`Issue`,
`Orientation`,
`DPI`,
`Color`,
`Cleaned`,
`Language`,
`MD5`,
`Extension`,
`Locator`,
`Library`,
`Commentary`,
`Series`,
`Periodical`,
`Coverurl`,
`UDC`,
`LBC`,
`ISSN`,
`DDC`,
`LCC`,
`Googlebookid`,
`Doi`,
`Searchable`,
`Bookmarked`,
`Scanned`,
`Paginated`,
`OpenLibraryID`,
`Filename`) 
VALUES(NOW(),
'".$id."',
'".trimarray($topic)."',
'".trimarray($author)."',
'".trimarray($title)."',
'".trimarray($volinfo)."',
'".trimarray($year)."',
'".trimarray($publisher)."',
'".trimarray($city)."',
'".trimarray($edition)."',
'".trimarray($identifier)."',
'".trimarray($asin)."',
'".trimarray($pages)."',
'".$_POST['Filesize']."',
'".trimarray($issue)."',
'".trimarray($orientation)."',
'".trimarray($dpi)."',
'".trimarray($color)."',
'".trimarray($cleaned)."',
'".trimarray($language)."',
'".$_POST['MD5']."',
'".$_POST['Extension']."',
'".trimarray($_POST['Locator'])."',
'".trimarray($library)."',
'".trimarray($commentary)."',
'".trimarray($series)."',
'".trimarray($periodical)."',
'".trimarray($coverurl)."',
'".trimarray($udc)."',
'".trimarray($lbc)."',
'".trimarray($issn)."',
'".trimarray($ddc)."',
'".trimarray($lcc)."',
'".trimarray($googlebookid)."',
'".trimarray($doi)."',
'".trimarray($searchable)."',
'".trimarray($bookmarked)."',
'".trimarray($scanned)."',
'".trimarray($paginated)."',
'".trimarray($openlibraryid)."',
'".trimarray($relPath)."')";
        

        $sql2="INSERT INTO `".$descrtable."` (`id`,`md5`,`descr`) VALUES ('".$id_descr."','".$_POST['MD5']."','".$descr."')";
	} else {
		$sql1="UPDATE `".$dbtable."` SET `Generic`='".trimarray($generic)."',
`Topic`='".trimarray($topic)."',
`Author`='".trimarray($author)."',
`Title`='".trimarray($title)."',
`VolumeInfo`='".trimarray($volinfo)."',
`Year`='".trimarray($year)."',
`Publisher`='".trimarray($publisher)."',
`City`='".trimarray($city)."',
`Edition`='".trimarray($edition)."',
`Identifier`='".trimarray($identifier)."',
`ASIN`='".trimarray($asin)."',
`Pages`='".trimarray($pages)."',
`Issue`='".trimarray($issue)."',
`Orientation`='".trimarray($orientation)."',
`DPI`='".trimarray($dpi)."',
`Color`='".trimarray($color)."',
`Cleaned`='".trimarray($cleaned)."',
`Language`='".trimarray($language)."',
`Extension`='".trimarray($_POST['Extension'])."',
`Locator`='".trimarray($_POST['Locator'])."',
`Library`='".trimarray($library)."',
`Commentary`='".trimarray($commentary)."',
`Series`='".trimarray($series)."',
`Periodical`='".trimarray($periodical)."',
`Coverurl`='".trimarray($coverurl)."',
`UDC`='".trimarray($udc)."',
`LBC`='".trimarray($lbc)."',
`ISSN`='".trimarray($issn)."',
`DDC`='".trimarray($ddc)."',
`LCC`='".trimarray($lcc)."',
`Googlebookid`='".trimarray($googlebookid)."',
`Doi`='".trimarray($doi)."',
`Searchable`='".trimarray($searchable)."',
`Bookmarked`='".trimarray($bookmarked)."',
`Scanned`='".trimarray($scanned)."',
`Paginated`='".trimarray($paginated)."',
`OpenLibraryID`='".trimarray($openlibraryid)."' WHERE `MD5`='".$_POST['MD5']."' LIMIT 1";
	    
        // check if there is a description for this book
        $tmpsql = "SELECT COUNT(*) FROM $descrtable WHERE md5='$_POST[MD5]'";
        $result = mysql_query($tmpsql,$con);
	    if (!$result) die($dberr);
        
        $row = mysql_fetch_assoc($result);
        if($row["COUNT(*)"] != 0 ) {
          $sql2="UPDATE `".$descrtable."` SET `descr`='".$descr."' WHERE `MD5`='".$_POST['MD5']."' LIMIT 1";  
        } else {
          $sql2="INSERT INTO `".$descrtable."` (`id`,`md5`,`descr`) VALUES ('".$id_descr."','".$_POST['MD5']."','".$descr."')";
        }
    }

	if (!mysql_query($sql1,$con))
		die('Error: ' . mysql_error());

	if (!mysql_query($sql2,$con))
		die('Error: ' . mysql_error() . '<br>Clean up the main table!');

	if (!$_POST['Edit']){
		$savedir = "{$repository}/{$savedir}";
		@mkdir($savedir,0777,true);
		$saveto = $savedir.'/'.$_POST['MD5'];










	//	$sql="UPDATE $dbtable SET `Filename`='$relPath' WHERE `ID`='$id' LIMIT 1";
	//	if (!mysql_query($sql,$con))
	//		die('Error: ' . mysql_error());






		flock($h,LOCK_UN);

		if(!copy($file,$saveto))
		//if(!rename($file,$saveto))
			die("<p>There was an error copying file ".$file.".");


		chmod($saveto,0777);

		//flock($h,LOCK_UN);
		fclose($h);


                         $prog = "cover-maker.py --id=".$id;
                         $args = $prog.' '.$repository.' A:/!genesis_covers';
                         system($args);
		         @unlink('tmpcover.ppm');
		         @unlink('tmpcover-000001.ppm');
		         @unlink($_POST['MD5']);
		 @unlink($file);
	}else{ //берем обложку если редактируется

if(substr($coverurl, 0,4) == 'http') {
$sqlselectid = "SELECT `ID` FROM ".$dbtable." where `md5` = '".$_POST['MD5']."' LIMIT 1";
//echo $sqlselectid;
        $resultid = mysql_query($sqlselectid,$con);
        $rowselectid = mysql_fetch_assoc($resultid);
        $idselect = $rowselectid['ID'];
//echo $idselect;

//echo 'A:/!genesis_covers/'.substr($idselect, 0, -3).'000/'.$_POST['MD5'].'-d.jpg';
                                      @unlink('A:/!genesis_covers/'.substr($idselect, 0, -3).'000/'.$_POST['MD5'].'-d.jpg');
                                      @unlink('A:/!genesis_covers/'.substr($idselect, 0, -3).'000/'.$_POST['MD5'].'-g.jpg');}

                         $prog = "cover-maker.py --only-dl --hash=".$_POST['MD5'];
                         $args = $prog.' '.$repository.' A:/!genesis_covers';
                        // echo $args;
                         @system($args);
		         @unlink('tmpcover.ppm');
		         @unlink('tmpcover-000001.ppm');



       }




	mysql_query('UNLOCK TABLES');
	// UNLOCK DB

	echo $htmlhead."<font color='#A00000'><h1>Регистрация завершена! \ Registration complete!</h1></font> MD5 хеш залитой книги: \ MD5 uploaded book:<br><font face='courier new'><b>$_POST[MD5]</b></font><p><h2>Спасибо! \ Thank you! </h2><a href=registration.php>Вернуться на страницу загрузки \ Go to the upload page</a><br/><a href=..>Вернуться на главную страницу \ Go to the main page</a><br/><a href=../book/index.php?md5=$_POST[MD5]>Посмотреть описание книги \ See description of the book</a>".$htmlfoot;
	






// removes unnecessary whitespace and tab characters
function clean($var){
	$c = "'\\";
	$str = str_replace("\t",' ',$_POST[$var]); // replace tabs
//   $str = preg_replace('/\s\s+/',' ',$str); // delete multiple spaces
	return trim(addcslashes($str,$c));
}

function mysql_next_id($dbtable) {
	$result = mysql_query("SHOW TABLE STATUS WHERE name='".$dbtable."'");
	$rows = mysql_fetch_assoc($result);
	return $rows['Auto_increment'];
}
mysql_close($con);
exit();
?>
