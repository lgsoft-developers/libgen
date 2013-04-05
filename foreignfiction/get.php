<?php

	include 'resume.php';


	@$con = mysql_connect('localhost','root','t7ijkp');
	if (!$con)
		die("<font color='#A00000'><h1>Error</h1></font>Could not connect to the database: ".mysql_error()."<br>Cannot proceed.<p>Please, report on the error from <a href=>the main page</a>.");

	mysql_query("SET session character_set_server = 'UTF8'");
	mysql_query("SET session character_set_connection = 'UTF8'");
	mysql_query("SET session character_set_client = 'UTF8'");
	mysql_query("SET session character_set_results = 'UTF8'");

	mysql_select_db('fiction',$con);



	$sql1 = "SELECT * FROM `main` WHERE MD5='".mysql_real_escape_string($_GET['md5'])."'";
echo $sql1;

	$result1 = mysql_query($sql1,$con);
	if (!$result1)
		die("<font color='#A00000'><h1>Error</h1></font>".mysql_error()."<br>Cannot proceed.<p>Please, report on the error from <a href=>the main page</a>.");



	$rows = mysql_fetch_assoc($result1);
	mysql_free_result($result1);
	mysql_close($con);


                                                        $Title = stripslashes($rows['Title']);
                                                        $AuthorFamily1 = stripslashes($rows['AuthorFamily1']);
                                                        $AuthorName1 = stripslashes($rows['AuthorName1']);
                                                        $AuthorSurname1 = stripslashes($rows['AuthorSurname1']);
                                                        $AuthorFamily2 = stripslashes($rows['AuthorFamily2']);
                                                        $AuthorName2 = stripslashes($rows['AuthorName2']);
                                                        $AuthorSurname2 = stripslashes($rows['AuthorSurname2']);
                                                        $AuthorFamily3 = stripslashes($rows['AuthorFamily3']);
                                                        $AuthorName3 = stripslashes($rows['AuthorName3']);
                                                        $AuthorSurname3 = stripslashes($rows['AuthorSurname3']);
                                                        $AuthorFamily4 = stripslashes($rows['AuthorFamily4']);
                                                        $AuthorName4 = stripslashes($rows['AuthorName4']);
                                                        $AuthorSurname4 = stripslashes($rows['AuthorSurname4']);
                                                        $Language = stripslashes($rows['Language']);
                                                        $Publisher = stripslashes($rows['Publisher']);
                                                        $Identifier = stripslashes($rows['Identifier']);
                                                        $Pages = stripslashes($rows['Pages']); if ($Pages==0) $Pages=''; 
                                                        $Series1 = stripslashes($rows['Series1']);
                                                        $Series2 = stripslashes($rows['Series2']);    
                                                        $Series3 = stripslashes($rows['Series3']); 
                                                        $Series4 = stripslashes($rows['Series4']);                                               
                                                        $Year = stripslashes($rows['Year']); if ($Year==0) $Year='';


    $downloadname = $AuthorFamily1.', '.$AuthorName1.' '.$AuthorSurname1.' & '.$AuthorFamily2.', '.$AuthorName2.' '.$AuthorSurname2.' & '.$AuthorFamily3.', '.$AuthorName3.' '.$AuthorSurname3.' - ['.$Series1.', '.$Series2.', '.$Series3.', '.$Series4.'] - '.$Title.' ('.$Year.', '.$Publisher.', '.$Identifier.')';
    $downloadname = str_replace(", ,", "", $downloadname);
    $downloadname = str_replace("[ , ]", "", $downloadname);
    $downloadname = str_replace("( , )", "", $downloadname);
    $downloadname = str_replace(" -  - ", " - ", $downloadname);
    $downloadname = str_replace(" & ,", " ", $downloadname);
    $downloadname = str_replace("( )", "", $downloadname);
    $downloadname = str_replace(", ]", "]", $downloadname);
    $downloadname = str_replace(", )", ")", $downloadname);
    $downloadname = str_replace(" )", ")", $downloadname);
    $downloadname = str_replace("  ", " ", $downloadname);
    $downloadname = str_replace("  ", " ", $downloadname);
    $downloadname = str_replace("( ", "(", $downloadname);
    $downloadname = str_replace(",   - ", "", $downloadname);
    $downloadname = str_replace("  ", " ", $downloadname);
    $downloadname = str_replace(" ]", "]", $downloadname);
    $downloadname = str_replace("[]", "", $downloadname);
    $downloadname = str_replace("(,", "(", $downloadname);
    $downloadname = str_replace(" -  - ", " - ", $downloadname);
    $downloadname = str_replace("/", " - ", $downloadname);
    $downloadname = str_replace(" & , - ", " - ", $downloadname);
    $ext = stripslashes($rows['Extension']);





       

if ($rows['ID']>1000000){$filename = 'R:\\!fiction\\'.substr($rows['ID'],0,4).'000\\'.$rows['MD5'].'.'.$rows['Extension'].'';}
else{$filename = 'R:\\!fiction\\'.substr($rows['ID'],0,3).'000\\'.$rows['MD5'].'.'.$rows['Extension'].'';}

echo $filename;
	if (!file_exists($filename)){
		die("<font color='#A00000'><h1>File not found!</h1></font>Please, report to the administrator.");
}
	new getresumable($filename,$ext,$downloadname);
?>
