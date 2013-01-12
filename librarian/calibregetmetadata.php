<?php

include('common/tnv_curl.php');


$md5 = $_GET['md5'];
$ext = $_GET['fileext'];

//переименовываем
rename("tmp\\".$md5, "tmp\\".$md5.".".$ext);


// извлекаем обложку

@$argumentscovers = '"C:\Program Files (x86)\Calibre2\ebook-meta.exe" tmp\\'.$md5.'.'.$ext.' --get-cover=A:\!genesis_covers\\'.$md5.'-temp.jpg > meta.txt';
@system($argumentscovers);
 if(file_exists('A:\!genesis_covers\\'.$md5.'-temp.jpg')){
@$gmcovers = 'http://libgen.org/covers/'.$md5.'-temp.jpg';}



//получаем метаинф. пишем в файл

@$args = '"C:\Program Files (x86)\Calibre2\ebook-meta.exe" tmp\\'.$md5.'.'.$ext.' > meta.txt';
@system($args);
@$cmresp = file_get_contents('meta.txt');




@unlink('meta.txt');

//переименовываем обратно

rename("tmp\\".$md5.".".$ext, "tmp\\".$md5);

$cmrespencoding = mb_detect_encoding($cmresp);

if($cmrespencoding ==  'UTF-8'){$cmrespencoding = 'cp1251';}

$cmresp = iconv($cmrespencoding, "UTF-8", $cmresp);


preg_match('|Series\s{1,20}:\s(.*?)\n|sei', $cmresp, $gmseries);
$gmseries = $gmseries[1];


preg_match('|Title\s{1,20}:\s(.*?)\n|sei', $cmresp, $gmtitle);
$gmtitle = $gmtitle[1];

preg_match('|Author\(s\)\s{1,20}:\s(.*?)\n|sei', $cmresp, $gmauthor);
$gmauthor = $gmauthor[1];

preg_match('|Publisher\s{1,20}:\s(.*?)\n|sei', $cmresp, $gmpublisher);
$gmpublisher = $gmpublisher[1];

preg_match('|Published\s{1,20}:\s(.*?)\n|sei', $cmresp, $gmyear);
$gmyear = $gmyear[1];
$gmyear = substr($gmyear, 0, 4);

preg_match('|Languages\s{1,20}:\s(.*?)\n|sei', $cmresp, $gmlang);
$gmlang = $gmlang[1];

function replacelang($gmlang){
     static $tbllang= array(
'abk' => 'Abkhaz',
'aar' => 'Afar',
'afr' => 'Afrikaans',
'aka' => 'Akan',
'sqi' => 'Albanian',
'gsw' => 'Alemannic',
'amh' => 'Amharic',
'ara' => 'Arabic',
'arg' => 'Aragonese',
'hye' => 'Armenian',
'asm' => 'Assamese',
'ava' => 'Avaric',
'ave' => 'Avestan',
'aym' => 'Aymara',
'aze' => 'Azerbaijani',
'bam' => 'Bambara',
'bak' => 'Bashkir',
'eus' => 'Basque',
'bel' => 'Belarusian',
'ben' => 'Bengali',
'bih' => 'Bihari',
'bis' => 'Bislama',
'bjn' => 'Banjar',
'bos' => 'Bosnian',
'bre' => 'Breton',
'bul' => 'Bulgarian',
'mya' => 'Burmese',
'cat' => 'Catalan',
'cha' => 'Chamorro',
'che' => 'Chechen',
'nya' => 'Chichewa',
'zho' => 'Chinese',
'chv' => 'Chuvash',
'cor' => 'Cornish',
'cos' => 'Corsican',
'cre' => 'Cree',
'hrv' => 'Croatian',
'ces' => 'Czech',
'dan' => 'Danish',
'day' => 'Dayak',
'div' => 'Divehi',
'nld' => 'Dutch',
'dzo' => 'Dzongkha',
'eng' => 'English',
'epo' => 'Esperanto',
'est' => 'Estonian',
'ewe' => 'Ewe',
'fao' => 'Faroese',
'fij' => 'Fijian',
'fin' => 'Finnish',
'fra' => 'French',
'ful' => 'Fula',
'glg' => 'Galician',
'kat' => 'Georgian',
'deu' => 'German',
'ell' => 'Greek, Modern',
'grn' => 'Guaraní',
'guj' => 'Gujarati',
'hat' => 'Haitian',
'hau' => 'Hausa',
'heb' => 'Hebrew',
'her' => 'Herero',
'hin' => 'Hindi',
'hmo' => 'HiriMotu',
'hun' => 'Hungarian',
'ina' => 'Interlingua',
'ind' => 'Indonesian',
'ile' => 'Interlingue',
'gle' => 'Irish',
'ibo' => 'Igbo',
'ipk' => 'Inupiaq',
'ido' => 'Ido',
'isl' => 'Icelandic',
'ita' => 'Italian',
'iku' => 'Inuktitut',
'jpn' => 'Japanese',
'jav' => 'Javanese',
'kal' => 'Greenlandic',
'kan' => 'Kannada',
'kau' => 'Kanuri',
'kas' => 'Kashmiri',
'kaz' => 'Kazakh',
'khm' => 'Khmer',
'kik' => 'Kikuyu, Gikuyu',
'kin' => 'Kinyarwanda',
'kir' => 'Kyrgyz',
'kom' => 'Komi',
'kon' => 'Kongo',
'kor' => 'Korean',
'kur' => 'Kurdish',
'kua' => 'Kwanyama',
'lat' => 'Latin',
'ltz' => 'Luxembourgish',
'lug' => 'Ganda',
'lim' => 'Limburgish',
'lin' => 'Lingala',
'lao' => 'Lao',
'lit' => 'Lithuanian',
'lub' => 'Luba-Katanga',
'lav' => 'Latvian',
'glv' => 'Manx',
'mkd' => 'Macedonian',
'mlg' => 'Malagasy',
'msa' => 'Malay',
'mal' => 'Malayalam',
'mlt' => 'Maltese',
'mri' => 'Māori',
'mar' => 'Marathi',
'mah' => 'Marshallese',
'mon' => 'Mongolian',
'nau' => 'Nauru',
'nav' => 'Navajo, Navaho',
'nob' => 'Norwegian',
'nde' => 'NorthNdebele',
'nep' => 'Nepali',
'ndo' => 'Ndonga',
'nno' => 'NorwegianNynorsk',
'nor' => 'Norwegian',
'iii' => 'Nuosu',
'nbl' => 'SouthNdebele',
'oci' => 'Occitan',
'oji' => 'Ojibwe',
'chu' => 'OldChurchSlavonic',
'orm' => 'Oromo',
'ori' => 'Oriya',
'oss' => 'Ossetian, Ossetic',
'pan' => 'Panjabi, Punjabi',
'pli' => 'Pāli',
'fas' => 'Persian',
'pol' => 'Polish',
'pus' => 'Pashto, Pushto',
'por' => 'Portuguese',
'que' => 'Quechua',
'roh' => 'Romansh',
'run' => 'Kirundi',
'ron' => 'Romanian',
'rus' => 'Russian',
'san' => 'Sanskrit',
'srd' => 'Sardinian',
'snd' => 'Sindhi',
'sme' => 'NorthernSami',
'smo' => 'Samoan',
'sag' => 'Sango',
'srp' => 'Serbian',
'gla' => 'Scottish',
'sna' => 'Shona',
'sin' => 'Sinhala, Sinhalese',
'slk' => 'Slovak',
'slv' => 'Slovene',
'som' => 'Somali',
'sot' => 'SouthernSotho',
'spa' => 'Spanish;Castilian',
'sun' => 'Sundanese',
'swa' => 'Swahili',
'ssw' => 'Swati',
'swe' => 'Swedish',
'tam' => 'Tamil',
'tel' => 'Telugu',
'tgk' => 'Tajik',
'tha' => 'Thai',
'tir' => 'Tigrinya',
'bod' => 'TibetanStandard',
'tuk' => 'Turkmen',
'tgl' => 'Tagalog',
'tsn' => 'Tswana',
'ton' => 'Tonga(TongaIslands)',
'tur' => 'Turkish',
'tso' => 'Tsonga',
'tat' => 'Tatar',
'twi' => 'Twi',
'tah' => 'Tahitian',
'uig' => 'Uighur, Uyghur',
'ukr' => 'Ukrainian',
'urd' => 'Urdu',
'uzb' => 'Uzbek',
'ven' => 'Venda',
'vie' => 'Vietnamese',
'vol' => 'Volapük',
'wln' => 'Walloon',
'cym' => 'Welsh',
'wol' => 'Wolof',
'fry' => 'WesternFrisian',
'xho' => 'Xhosa',
'yid' => 'Yiddish',
'yor' => 'Yoruba',
'zha' => 'Zhuang, Chuang',
'zul' => 'Zulu',
'ab' => 'Abkhaz',
'aa' => 'Afar',
'af' => 'Afrikaans',
'ak' => 'Akan',
'sq' => 'Albanian',
'am' => 'Amharic',
'ar' => 'Arabic',
'an' => 'Aragonese',
'hy' => 'Armenian',
'as' => 'Assamese',
'av' => 'Avaric',
'ae' => 'Avestan',
'ay' => 'Aymara',
'az' => 'Azerbaijani',
'bm' => 'Bambara',
'ba' => 'Bashkir',
'eu' => 'Basque',
'be' => 'Belarusian',
'bn' => 'Bengali',
'bh' => 'Bihari',
'bi' => 'Bislama',
'bjn' => 'Banjar',
'bs' => 'Bosnian',
'br' => 'Breton',
'bg' => 'Bulgarian',
'my' => 'Burmese',
'ca' => 'Catalan',
'ch' => 'Chamorro',
'ce' => 'Chechen',
'ny' => 'Chichewa',
'zh' => 'Chinese',
'cv' => 'Chuvash',
'kw' => 'Cornish',
'co' => 'Corsican',
'cr' => 'Cree',
'hr' => 'Croatian',
'cs' => 'Czech',
'da' => 'Danish',
'day' => 'Dayak',
'dv' => 'Divehi',
'nl' => 'Dutch',
'dz' => 'Dzongkha',
'en' => 'English',
'eo' => 'Esperanto',
'et' => 'Estonian',
'ee' => 'Ewe',
'fo' => 'Faroese',
'fj' => 'Fijian',
'fi' => 'Finnish',
'fr' => 'French',
'ff' => 'Fula',
'gl' => 'Galician',
'ka' => 'Georgian',
'de' => 'German',
'el' => 'Greek, Modern',
'gn' => 'Guaraní',
'gu' => 'Gujarati',
'ht' => 'Haitian',
'ha' => 'Hausa',
'he' => 'Hebrew',
'hz' => 'Herero',
'hi' => 'Hindi',
'ho' => 'HiriMotu',
'hu' => 'Hungarian',
'ia' => 'Interlingua',
'id' => 'Indonesian',
'ie' => 'Interlingue',
'ga' => 'Irish',
'ig' => 'Igbo',
'ik' => 'Inupiaq',
'io' => 'Ido',
'is' => 'Icelandic',
'it' => 'Italian',
'iu' => 'Inuktitut',
'ja' => 'Japanese',
'jv' => 'Javanese',
'kl' => 'Greenlandic',
'kn' => 'Kannada',
'kr' => 'Kanuri',
'ks' => 'Kashmiri',
'kk' => 'Kazakh',
'km' => 'Khmer',
'ki' => 'Kikuyu, Gikuyu',
'rw' => 'Kinyarwanda',
'ky' => 'Kyrgyz',
'kv' => 'Komi',
'kg' => 'Kongo',
'ko' => 'Korean',
'ku' => 'Kurdish',
'kj' => 'Kwanyama',
'la' => 'Latin',
'lb' => 'Luxembourgish',
'lg' => 'Ganda',
'li' => 'Limburgish',
'ln' => 'Lingala',
'lo' => 'Lao',
'lt' => 'Lithuanian',
'lu' => 'Luba-Katanga',
'lv' => 'Latvian',
'gv' => 'Manx',
'mk' => 'Macedonian',
'mg' => 'Malagasy',
'ms' => 'Malay',
'ml' => 'Malayalam',
'mt' => 'Maltese',
'mi' => 'Māori',
'mr' => 'Marathi(Marāṭhī)',
'mh' => 'Marshallese',
'mn' => 'Mongolian',
'na' => 'Nauru',
'nv' => 'Navajo, Navaho',
'nb' => 'NorwegianBokmål',
'nd' => 'NorthNdebele',
'ne' => 'Nepali',
'ng' => 'Ndonga',
'nn' => 'NorwegianNynorsk',
'no' => 'Norwegian',
'ii' => 'Nuosu',
'nr' => 'SouthNdebele',
'oc' => 'Occitan',
'oj' => 'Ojibwe, Ojibwa',
'cu' => 'OldChurchSlavonic',
'om' => 'Oromo',
'or' => 'Oriya',
'os' => 'Ossetian, Ossetic',
'pa' => 'Panjabi, Punjabi',
'pi' => 'Pāli',
'fa' => 'Persian',
'pl' => 'Polish',
'ps' => 'Pashto, Pushto',
'pt' => 'Portuguese',
'qu' => 'Quechua',
'rm' => 'Romansh',
'rn' => 'Kirundi',
'ro' => 'Romanian',
'ru' => 'Russian',
'sa' => 'Sanskrit',
'sc' => 'Sardinian',
'sd' => 'Sindhi',
'se' => 'NorthernSami',
'sm' => 'Samoan',
'sg' => 'Sango',
'sr' => 'Serbian',
'gd' => 'Scottish',
'sn' => 'Shona',
'si' => 'Sinhala',
'sk' => 'Slovak',
'sl' => 'Slovene',
'so' => 'Somali',
'st' => 'SouthernSotho',
'es' => 'Spanish',
'su' => 'Sundanese',
'sw' => 'Swahili',
'ss' => 'Swati',
'sv' => 'Swedish',
'ta' => 'Tamil',
'te' => 'Telugu',
'tg' => 'Tajik',
'th' => 'Thai',
'ti' => 'Tigrinya',
'bo' => 'Tibetan',
'tk' => 'Turkmen',
'tl' => 'Tagalog',
'tn' => 'Tswana',
'to' => 'Tonga',
'tr' => 'Turkish',
'ts' => 'Tsonga',
'tt' => 'Tatar',
'tw' => 'Twi',
'ty' => 'Tahitian',
'ug' => 'Uighur',
'uk' => 'Ukrainian',
'ur' => 'Urdu',
'uz' => 'Uzbek',
've' => 'Venda',
'vi' => 'Vietnamese',
'vo' => 'Volapük',
'wa' => 'Walloon',
'cy' => 'Welsh',
'wo' => 'Wolof',
'fy' => 'Western Frisian',
'xh' => 'Xhosa',
'yi' => 'Yiddish',
'yo' => 'Yoruba',
'za' => 'Zhuang',
'zu' => 'Zulu'
);
        return strtr($gmlang, $tbllang);    
}
$gmlang = replacelang($gmlang);

preg_match('|Book\sProducer\s{1,20}:\s(.*?)\n|sei', $cmresp, $gmcomment);
$gmcomment = $gmcomment[1];

preg_match('|Comments\s{1,20}:\s(.*?)$|sei', $cmresp, $gmdescr);
$gmdescr = $gmdescr[1];


preg_match('|Identifiers\s{1,20}:\s(.*?)\n|sei', $cmresp, $gmident);
$gmident = $gmident[1];

preg_match('|isbn:[0-9X-x-]{10,17}|', $gmident, $gmisbn);
$gmisbn = $gmisbn[0];
$gmisbn = str_replace('isbn:', '', $gmisbn);


preg_match('|amazon:[0-9X-x-]{10,17}|', $gmident, $gmamzn);
$gmamzn = $gmamzn[0];
$gmamzn = str_replace('amazon:', '', $gmamzn);


$gmisbn = $gmisbn.','.$gmamzn;


preg_match('|google:[A-Za-z0-9_]{12}|', $gmident, $gmgbid);
$gmgbid = $gmgbid[0];
$gmgbid = str_replace('google:', '', $gmgbid);




?>