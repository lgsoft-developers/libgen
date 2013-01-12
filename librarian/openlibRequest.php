<?php

include('common/tnv_curl.php');


//$olid = 'OL8051797M';

//получаем json
if (@fopen('http://openlibrary.org/books/'.$olid.'.json', "r")){$resp = get_content('http://openlibrary.org/books/'.$olid.'.json');}
else{$resp = get_content('http://openlibrary.org/works/'.$olid.'.json');}
$a1=json_decode($resp['content'],true);

//получаем wiki citation
if (@fopen('http://openlibrary.org/books/'.$olid, "r")){$resppage = get_content('http://openlibrary.org/books/'.$olid);}
else{$resppage = get_content('http://openlibrary.org/works/'.$olid);}
preg_match_all('~\{\{Citation(.*?)\}\}~isU', $resppage['content'], $wikicit, PREG_PATTERN_ORDER);
//echo $wikicit[0][0];




//print_r($a1);


$a1subtitle = $a1['subtitle'];
//echo "<br>".$a1subtitle." - подзаголовок<br>";

$a1coverscount = count($a1['covers']);
for ($i = 0; $i <= $a1coverscount; $i++){
$a1covers[] = $a1['covers'][$i]; }
$a1coversall = implode('', $a1covers);
if($a1['covers'] != '' ) $a1coversall = 'http://covers.openlibrary.org/b/id/'.$a1coversall.'-L.jpg';

//echo $a1coversall." - обложки<br>";

$a1lc_classificationscount = count($a1['lc_classifications']);
for ($i = 0; $i <= $a1lc_classificationscount; $i++){
$a1lc_classifications[] = $a1['lc_classifications'][$i]; }
$a1lc_classificationsall = implode('; ', $a1lc_classifications);
//echo $a1lc_classificationsall."<br>";

$a1title = $a1['title'];
//echo $a1title."- заголовок<br>";

$a1subjectscount = count($a1['subjects']);
for ($i = 0; $i <= $a1subjectscount; $i++){
$a1subjects[] = $a1['subjects'][$i]; }
$a1subjectsall = implode('; ', $a1subjects);
//echo $a1subjectsall." - тематики <br>";

$a1publish_country = $a1['publish_country'];
//echo $a1publish_country." - страна публикации<br>";




//$a1by_statement = $a1['by_statement'];
//echo $a1by_statement." - авторы<br>"; //автор
preg_match_all('~\|author[0-9]{0,1}\s=\s(.*)\n~isU', $wikicit[0][0],  $a1by_statement, PREG_PATTERN_ORDER);

$a1by_statement = implode(', ', $a1by_statement[1]);





$a1publish_placescount = count($a1['publish_places']);
for ($i = 0; $i <= $a1publish_placescount; $i++){
$a1publish_places[] = $a1['publish_places'][$i]; }
$a1publish_placesall = implode('; ', $a1publish_places);
//echo $a1publish_placesall." - место издания<br>";

$a1number_of_pages = $a1['number_of_pages'];
//echo $a1number_of_pages." - страницы<br>";


$a1dewey_decimal_classcount = count($a1['dewey_decimal_class']);
for ($i = 0; $i <= $a1dewey_decimal_classcount; $i++){
$a1dewey_decimal_class[] = $a1['dewey_decimal_class'][$i]; }
$a1dewey_decimal_classall = implode('; ', $a1dewey_decimal_class);
//echo $a1dewey_decimal_classall." - DDC<br>";



$a1lccncount = count($a1['lccn']);
for ($i = 0; $i <= $a1lccncount; $i++){
$a1lccn[] = $a1['lccn'][$i]; }
$a1lccnall = implode('; ', $a1lccn);
//echo $a1lccnall." - LCC<br>";


$a1publisherscount = count($a1['publishers']);
for ($i = 0; $i <= $a1publisherscount; $i++){
$a1publishers[] = $a1['publishers'][$i]; }
$a1publishersall = implode('; ', $a1publishers);
//echo $a1publishersall." Издатель<br>";




$a1isbn_13count = count($a1['isbn_13']);
for ($i = 0; $i <= $a1isbn_13count; $i++){
$a1isbn_13[] = $a1['isbn_13'][$i]; }
$a1isbn_13all = implode(', ', $a1isbn_13);
//echo $a1isbn_13all." - ISBN13<br>";


$a1isbn_10count = count($a1['isbn_10']);
for ($i = 0; $i <= $a1isbn_10count; $i++){
$a1isbn_10[] = $a1['isbn_10'][$i]; }
$a1isbn_10all = implode(', ', $a1isbn_10);
//echo $a1isbn_10all." - ISBN10<br>";



$a1edition_name = $a1['edition_name'];
//echo $a1edition_name." Издание<br>";



$a1languagescount = count($a1['languages']);
for ($i = 0; $i <= $a1languagescount; $i++){
$a1languages[] = $a1['languages'][$i]['key']; }
$a1languagesall = implode(', ', $a1languages);
//echo $a1languagesall." Язык<br>";
$a1languagesall = str_replace('/languages/', '', $a1languagesall);


$a1oclc_numberscount = count($a1['oclc_numbers']);
for ($i = 0; $i <= $a1oclc_numberscount; $i++){
$a1oclc_numbers[] = $a1['oclc_numbers'][$i]; }
$a1oclc_numbersall = implode('; ', $a1oclc_numbers);
//echo $a1oclc_numbersall." OCLC<br>";

$a1publish_date = $a1['publish_date'];
//echo $a1publish_date." - Дата издания<br>";


////////////////////////////
$a1isbnall = $a1isbn_10all.','.$a1isbn_13all;
//echo $a1isbnall." <br>";
$a1isbnall = trim($a1isbnall);
$a1isbnall = trim($a1isbnall, ',');
$a1isbnall = str_replace(" ", "", $a1isbnall);
$a1isbnall = str_replace(",,", ",", $a1isbnall);

$a1titlesall = $a1title.'. '.$a1subtitle;


$a1series = $a1['series'];
$a1seriesall = implode(',', $a1series);
if(!$a1series){if (preg_match("|([^\(]*)\((.*)\)|sei", $a1titlesall, $arrtitle)) {$a1seriesall = $arrtitle[2];
            $a1seriesalla = $arrtitle[1];
            $a1titlesall = $a1seriesalla; }
            else {$a1seriesall=""; 
            $a1titlesall = $a1titlesall;}}



?>