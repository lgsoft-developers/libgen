<?php

function RGBitem($record, $tag, $code)
{

    $datafield = split('<datafield tag="' . $tag . '"', $record);
    $datafield = split('</datafield>', $datafield[1]);

    $subfield = split('<subfield code="' . $code . '">', $datafield[0]);
    $subfield = split('</subfield>', $subfield[1]);

    return trim($subfield[0]);
}

function RGBitems($record, $tag, $code)
{
	$result = array();

    $datafields = split('<datafield tag="' . $tag . '"', $record);

    for($i=1; $i<sizeof($datafields); $i++){

		$field = split('</datafield>', $datafields[$i]);
		$subfields = split('<subfield code="' . $code . '">', $field[0]);
		for($j=1; $j<sizeof($subfields); $j++){
			$subfield = split('</subfield>', $subfields[$j]);
			$subfield = split('\(', $subfield[0]);
			array_push($result, trim($subfield[0]));
		}
	}

    return $result;
}

$request = "http://lbc.rsl.ru/bib4md5/zg/zg.php?server=RSL&Undef=" . $isbn;
$response = file_get_contents($request);
//echo $response;

$records = split('<record xmlns="http://www.loc.gov/MARC21/slim">', $response);

    if (count($records) < 2)
        echo 'Could not find item.';

    else {

        for ($i = 1; $i < count($records); $i++) {

            // Title
			$rsltitle = implode(' ', array_filter(array(
					0 => implode(', ', RGBitems($records[$i], '245', 'a')),
					1 => implode(', ', RGBitems($records[$i], '245', 'b')),
					//2 => RGBitem($records[$i], '245', 'c'),
					3 => implode(', ', RGBitems($records[$i], '245', 'p')),
                    4 => implode(', ', RGBitems($records[$i], '773', 'h'))
				)));
            // Author
			$author_100_a = implode('; ', RGBitems($records[$i], '100', 'a'));
			if($author_100_a != '') $author1 = $author_100_a;
			else{
				$author_245_c = implode('; ', RGBitems($records[$i], '245', 'c'));
				if($author_245_c != '') $author1 = $author_245_c;
				else{
					$author1 = implode('; ', RGBitems($records[$i], '600', 'a'));
				}
			}
			$rslauthor = implode(';', array_filter(array(
					0 => $author1,
					1 => implode('; ', RGBitems($records[$i], '700', 'a')),
					2 => implode('; ', RGBitems($records[$i], '700', 'e'))
				)));
echo $author;

            $rslcity = implode(', ', RGBitems($records[$i], '260', 'a'));
            $rslpublisher = implode(', ', array_filter(array(
					0 => implode(', ', RGBitems($records[$i], '260', 'b')),
                    1 => implode(', ', RGBitems($records[$i], '773', 't'))
                )));
            $rslyear = RGBitem($records[$i], '260', 'c');
            $rsledition = RGBitem($records[$i], '250', 'a');
            $rslvolumeninfo = implode(', ', array_filter(array(
					0 => implode(', ', RGBitems($records[$i], '245', 'n')),
					1 => implode(', ', RGBitems($records[$i], '245', 's')),
                    2 => implode(', ', RGBitems($records[$i], '773', 'g'))
				)));
            $rslpages = RGBitem($records[$i], '300', 'a');
            $rslidentifier = implode(', ',RGBitems($records[$i], '020', 'a'));
            $rsllanguage = implode(', ', RGBitems($records[$i], '041', 'a'));
            $rsllanguage = str_replace('rus', 'Russian', $rsllanguage);

            $topic1 = implode(', ', RGBitems($records[$i], '650', 'a'));
            $topic2 = implode(', ', RGBitems($records[$i], '653', 'a'));
            $rsltopic = trim(trim($topic1 . ',' . $topic2, '()'), ';');
           $rsltopic = substr($rsltopic, 0, 499);

            $rsludc = implode(', ', RGBitems($records[$i], '080', 'a'));
			$bbc = implode('', array_filter(array(
					0 => implode(', ', RGBitems($records[$i], '084', 'a')),
					1 => implode(', ', RGBitems($records[$i], '084', '2')),
				)));
            $rslseries = implode('; ', array_filter(array(
					0 => implode(', ', RGBitems($records[$i], '490', 'a')),
					1 => implode(', ', RGBitems($records[$i], '490', 'v')),
					2 => implode(', ', RGBitems($records[$i], '490', 'x')),
					3 => implode(', ', RGBitems($records[$i], '440', 'a')),
					4 => implode(', ', RGBitems($records[$i], '440', 'p')),
					5 => implode(', ', RGBitems($records[$i], '440', 'n')),
					6 => implode(', ', RGBitems($records[$i], '440', 'v')),
					7 => implode(', ', RGBitems($records[$i], '440', 'x'))
				)));
			$rsldescription = implode('; ', array_filter(array(
					0 => implode(', ', RGBitems($records[$i], '505', 'a')),
					1 => implode(', ', RGBitems($records[$i], '505', 't')),
					2 => implode(', ', RGBitems($records[$i], '505', 'r')),
					3 => implode(', ', RGBitems($records[$i], '520', 'a'))
				)));

        }
    }
unset($response);
?>