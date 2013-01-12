<?php

include('common/tnv_curl.php');

if(strlen($oclc)<10)
    {$resp = get_content('http://www.worldcat.org/oclc/'.$oclc);} 

else
    {$searchresp = get_content('http://www.worldcat.org/search?q='.$oclc.'&qt=owc_search');
//print_r($searchresp);
		if(preg_match('|<td class="num">1.</td>|',$searchresp['content']))
		{preg_match_all('~<div class="oclc_number">(.*)</div>~isU', str_replace("\r\n", "", str_replace("\t","",$searchresp['content'])), $oclc, PREG_PATTERN_ORDER);
		         $oclc =  $oclc[1][0];
		 	 $resp = get_content('http://www.worldcat.org/oclc/'.$oclc);}
                 else
       		 {$resp = ''; $wcerror = 'Could not find item';}

    }





$resp = str_replace("\r\n", "", str_replace("\t","",$resp['content']));
preg_match_all('~<th>ISBN:</th>\n\s{0,10}<td>(.*)</td>~isU', $resp, $wcisbn, PREG_PATTERN_ORDER);
$wcisbn = str_replace(',,', ',', str_replace(' ', ',', $wcisbn[1][0]));


preg_match_all('~<div id="summary">(.*)</td>~isU', $resp, $wcdescr1, PREG_PATTERN_ORDER);
$wcdescr1 =  $wcdescr1[1][0];


preg_match_all('~<th>Contents:</th>\n\s{0,10}<td>(.*)</td>~isU', $resp, $wcdescr2, PREG_PATTERN_ORDER);
$wcdescr2 =  $wcdescr2[1][0];

$wcdescr = $wcdescr1."\n".$wcdescr2;

preg_match_all('~<th>Responsibility:</th>\n\s{0,10}<td>(.*)</td>~isU', $resp, $wcauth, PREG_PATTERN_ORDER);
$wcauth = $wcauth[1][0];


preg_match_all('~<h1 class="title">(.*)</h1>~isU', $resp, $wctitle, PREG_PATTERN_ORDER);
$wctitle = $wctitle[1][0];

preg_match_all('~<img class="cover" border="0" src=\"(.*)\?~isU', $resp, $wccover, PREG_PATTERN_ORDER);
$wccover = 'http:'.$wccover[1][0];
$wccoverget = get_content($wccover);
if(md5($wccoverget['content']) == '9dbb98ea69e203f868aef752387108ed') {$wccover = '';}
unset($wccoverget);
if($resp == ''){$wccover = '';}


preg_match_all('~<td id="bib-hotSeriesTitles-cell">(.*)</td>~isU', $resp, $wcseries, PREG_PATTERN_ORDER);
$wcseries = strip_tags($wcseries[1][0]);



//парсим паблишера
preg_match_all('~<td id="bib-publisher-cell">(.*)</td>~isU', $resp, $wcpublication, PREG_PATTERN_ORDER);
$wcpublication = trim(str_replace(',,', ',', str_replace('&copy;', '', strip_tags($wcpublication[1][0]))));

$wcpublication = explode(':', $wcpublication);

if(count($wcpublication) == 2){
$wccity = $wcpublication[0]; 
$wcpubyear = explode(',', $wcpublication[1]);

$wcpubl = $wcpubyear[0];
$wcyear = $wcpubyear[1];

}
elseif(count($wcpublication) == 1){

preg_match_all('~([[:punct:]]|\s){1}[0-9]{4}([[:punct:]]|\s){1}$~isU', $wcpublication[0], $wcyear, PREG_PATTERN_ORDER);



$wcpubl = str_replace($wcyear[0][0], '', $wcpublication[0]);
$wcyear = preg_replace('~[[:punct:]]~', '', $wcyear[0][0]);
}






//парсим издание - язык, издание

preg_match_all('~<td id="bib-itemType-cell">(.*)</td>~isU', $resp, $wceditorial, PREG_PATTERN_ORDER);
$wceditorial = strip_tags($wceditorial[1][0]);


$wceditorial = explode(':', $wceditorial);

if(count($wceditorial) == 2) {$wclang = str_replace('View all editions and formats', '', $wceditorial[1]);}
elseif(count($wceditorial) == 3) {$wclang = str_replace('View all editions and formats', '', $wceditorial[1]); $wcedit = str_replace('View all editions and formats', '', $wceditorial[2]);}


preg_match_all('~<tr id="details-description">(.*)</tr>~isU', $resp, $wcpagination, PREG_PATTERN_ORDER);

preg_match_all('~<td>(.*)</td>~isU', $wcpagination[1][0], $wcpage, PREG_PATTERN_ORDER);
$wcpage = $wcpage[1][0];

preg_match_all('~<ul id="subject-terms-detailed">(.*)</ul>~isU', $resp, $wctopic, PREG_PATTERN_ORDER);
$wctopic = strip_tags($wctopic[1][0]);

//echo $wctopic;



?>