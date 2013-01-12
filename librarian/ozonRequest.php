<?php

function xml2array($xml) {
        $xmlary = array();
               
        $reels = '/<(\w+)\s*([^\/>]*)\s*(?:\/>|>(.*)<\/\s*\\1\s*>)/s';
        $reattrs = '/(\w+)=(?:"|\')([^"\']*)(:?"|\')/';

        preg_match_all($reels, $xml, $elements);

        foreach ($elements[1] as $ie => $xx) {
                $xmlary[$ie]["name"] = $elements[1][$ie];
               
                if ($attributes = trim($elements[2][$ie])) {
                        preg_match_all($reattrs, $attributes, $att);
                        foreach ($att[1] as $ia => $xx)
                                $xmlary[$ie]["attributes"][$att[1][$ia]] = $att[2][$ia];
                }

                $cdend = strpos($elements[3][$ie], "<");
                if ($cdend > 0) {
                        $xmlary[$ie]["text"] = substr($elements[3][$ie], 0, $cdend - 0);
                }

                if (preg_match($reels, $elements[3][$ie]))
                        $xmlary[$ie]["elements"] = xml2array($elements[3][$ie]);
                else if ($elements[3][$ie]) {
                        $xmlary[$ie]["text"] = $elements[3][$ie];
                }
        }

        return $xmlary;
}


function ozon_request($isbn)
{
    
    // some paramters
    $method = "GET";
    $host = "www.ozon.ru";

    // create request
    $request = "/webservice/webservice.asmx/SearchWebService?searchText=".$isbn."&searchContext=";
    
    // do request
    $response = file_get_contents("http://".$host.$request);
//echo  $response;

    if ($response === False)
    {
        return False;
    }
    else
    {
        // parse XML
        $xmlAr = xml2array($response);
        
        return $xmlAr;
        
    }
}


$ozonInfo = array('error'=>'');
$ozonXmlArray = ozon_request($isbn);
//print_r($ozonXmlArray);

if ($ozonXmlArray === False)
 {
     $ozonInfo['error'] = "Did not work.\n";
 }
 else
 {
     if ($ozonXmlArray[14]['elements'][0]['elements'][1]['text'] != '')
     {
         
         $ozonInfo['ID'] = $ozonXmlArray[14]['elements'][0]['elements'][0]['text'];
         $ozonInfo['Title'] = $ozonXmlArray[14]['elements'][0]['elements'][1]['text'];
         $ozonInfo['Year'] = $ozonXmlArray[14]['elements'][0]['elements'][5]['text'];
         $ozonInfo['Picture'] = $ozonXmlArray[14]['elements'][0]['elements'][13]['text'];
        



	
    $htmlcode = file_get_contents("http://www.ozon.ru/context/detail/id/".$ozonInfo['ID']."/");
    $htmlcode = str_replace("\t", "", str_replace("\r\n", "", iconv('windows-1251', 'utf-8//IGNORE', $htmlcode)));

//echo $htmlcode;

preg_match_all ('|<p>ISBN(.*);|iU', $htmlcode, $ozonisbn, PREG_SET_ORDER);
$ozonInfo['ISBN'] = strip_tags($ozonisbn[0][1]);


preg_match_all ('|<p>Издательство:(.*)</p>|iU', $htmlcode, $ozonpubl, PREG_SET_ORDER);
$ozonInfo['Publisher'] = strip_tags($ozonpubl[0][1]);

preg_match_all ('|<!-- Data\[COMMENT\] -->(.*)</td>|iU', $htmlcode, $ozonedit, PREG_SET_ORDER);
$ozonInfo['Edition'] = strip_tags($ozonedit[0][1]);

preg_match_all ('|<p>Серия:(.*)</p>|iU', $htmlcode, $ozonser, PREG_SET_ORDER);
$ozonInfo['Series'] = strip_tags($ozonser[0][1]);

preg_match_all ('~<p>(Автор: |Авторы: |Редактор: |Редакторы: | Составитель: | Составители: |Художник: |Художники: |Переводчик: |Переводчики: )(.*)</p>~iU', $htmlcode, $ozonauth, PREG_SET_ORDER);
if(count($ozonauth) == 2){$ozonauth1 = strip_tags($ozonauth[0][0]); $ozonauth2 = strip_tags($ozonauth[1][0]);
$ozonauth1 = strstr($ozonauth1, ':').' ('.mb_substr($ozonauth1, 0,6).'.)';
$ozonauth2 = strstr($ozonauth2, ':').' ('.mb_substr($ozonauth2, 0,6).'.)';
$ozonInfo['Author'] = $ozonauth1.'; '.$ozonauth2;
}else{$ozonauth1 = strip_tags($ozonauth[0][0]);
$ozonInfo['Author'] = strstr($ozonauth1, ':').' ('.mb_substr($ozonauth1, 0,6).'.)';}



preg_match_all ('|Страниц</span></div><div class="techDescr"><span>(.*)\sстр.|iU', $htmlcode, $ozonpag, PREG_SET_ORDER);
$ozonInfo['Pages'] = strip_tags($ozonpag[0][1]);

preg_match_all ('|<h3>Каталог</h3>(.*)</div>|iU', $htmlcode, $ozontopic, PREG_SET_ORDER);
$ozonInfo['Topic'] = str_replace('»', '//', strip_tags($ozontopic[0][1]));

preg_match_all ('|<p>Языки:\s(.*)</p>|iU', $htmlcode, $ozonlang, PREG_SET_ORDER);
$ozonInfo['Language'] = trim(strip_tags($ozonlang[0][1]));
if($ozonInfo['Language'] == 'Русский'){$ozonInfo['Language'] = 'Russian';}
elseif($ozonInfo['Language'] == 'Английский'){$ozonInfo['Language'] = 'English';}

preg_match_all ('|<!-- Data\[ANNOTATION\] -->(.*)</td>|iU', $htmlcode, $ozonann, PREG_SET_ORDER);
$ozonInfo['Annotation'] = trim(strip_tags($ozonann[0][1]));



     }else
     {
         $ozonInfo['error'] = "Could not find item.\n";
     }
 }


?>
