<?php

include('common/tnv_curl.php');



$resp = get_content('http://books.google.com/books/download/?id='.$bgid.'&hl=en&output=bibtex');
$resp = $resp['content'];

$resp = str_replace('\\', '', $resp);
$resp = str_replace('=', '":', $resp);
$resp = str_replace('{', '"', $resp);
$resp = str_replace('}', '"', $resp);
$resp = str_replace(",\n", ',"', $resp);
$resp = str_replace('@book', '"book":', $resp);
$resp = "{".$resp."}";
$resp = str_replace("\n", '', $resp);
$resp = str_replace('  ', ' ', $resp);
$resp = str_replace('" ', '"', $resp);
$resp = str_replace('":"', '": "', $resp);
$resp = str_replace('books?id":', 'books?id=', $resp);
$resp = str_replace(',"', '","', $resp);
$resp = str_replace('""', '"', $resp);


//echo $resp;
$b = json_decode($resp, true);

//print_r($b);

@$bcover = 'http://bks8.books.google.com/books?id='.$bgid.'&printsec=frontcover&img=1&zoom=1';


$bcoverget = get_content($bcover);
if(md5($bcoverget['content']) == 'e89e0e364e83c0ecfba5da41007c9a2c') {$bcover = '';}
unset($bcoverget);

@$btitle = $b['title'];
@$bauthor = $b['author'];
@$bseries = $b['series'];
@$byear = $b['year'];
@$blccn = $b['lccn'];
//@$bisbn = $b['isbn'];
@$bpublisher = $b['publisher'];


$bdescr = get_content('http://books.google.com/books/download/?id='.$bgid.'&hl=en');

$bdescr = $bdescr['content'];
//echo $bdescr;
preg_match_all ('|<div id=synopsistext dir=ltr class="sa">(.*)</div>|isU', $bdescr, $bdescr2, PREG_SET_ORDER);
$bdescr2 = $bdescr2['0']['1'];


preg_match_all ('|<table id="metadata_content_table">(.*)</table>|isU', $bdescr, $bmetadata, PREG_SET_ORDER);
$bmetadata = $bmetadata[0][0];
//echo $bmetadata;

preg_match_all ('|Length</span></td><td class="metadata_value"><span dir=ltr>(.*) pages|isU', $bmetadata, $bpages, PREG_SET_ORDER);
//print_r($bpages);
$bpages = $bpages['0']['1'];


preg_match_all ('|ISBN</span>(.*)</span>|isU', $bmetadata, $bisbn, PREG_SET_ORDER);
//print_r($bisbn);
$bisbn = str_replace('</td><td class="metadata_value"><span dir=ltr>', '', $bisbn['0']['1']);


preg_match_all ('|Edition</span>(.*)</span>|isU', $bmetadata, $bedit, PREG_SET_ORDER);

//print_r($bedit);
$bedit = str_replace('</td><td class="metadata_value"><span dir=ltr>', '', $bedit['0']['1']);


//echo $bedit;

unset($bdescr);
?>