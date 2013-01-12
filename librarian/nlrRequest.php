<?php

function NLRitem($record, $tag, $code)
{

    $datafield = split('<datafield tag="' . $tag . '"', $record);
    $datafield = split('</datafield>', $datafield[1]);

    $subfield = split('<subfield code="' . $code . '">', $datafield[0]);
    $subfield = split('</subfield>', $subfield[1]);

    return trim($subfield[0]);
}

function NLRitems($record, $tag, $code)
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

$request = "http://lbc.rsl.ru/bib4md5/zg/zg.php?server=NLR&ISBN=" . $isbn;
$response = file_get_contents($request);
$records = split('<record xmlns="http://www.loc.gov/MARC21/slim">', $response);

    if (count($records) < 2)
        echo 'Could not find item or connection timeout';

    else {

        for ($i = 1; $i < count($records); $i++) {

            // Title
			$title = implode(' ', array_filter(array(
					0 => implode(', ', NLRitems($records[$i], '245', 'a')),
					1 => implode(', ', NLRitems($records[$i], '245', 'b')),
					//2 => NLRitem($records[$i], '245', 'c'),
					3 => implode(', ', NLRitems($records[$i], '245', 'p')),
                    4 => implode(', ', NLRitems($records[$i], '773', 'h'))
				)));
            // Author
			$author_100_a = implode('; ', NLRitems($records[$i], '100', 'a'));
			if($author_100_a != '') $author1 = $author_100_a;
			else{
				$author_245_c = implode('; ', NLRitems($records[$i], '245', 'c'));
				if($author_245_c != '') $author1 = $author_245_c;
				else{
					$author1 = implode('; ', NLRitems($records[$i], '600', 'a'));
				}
			}
			$author = implode(';', array_filter(array(
					0 => $author1,
					1 => implode('; ', NLRitems($records[$i], '700', 'a')),
					2 => implode('; ', NLRitems($records[$i], '700', 'e'))
				)));
            $city = implode(', ', NLRitems($records[$i], '260', 'a'));
            $publisher = implode(', ', array_filter(array(
					0 => implode(', ', NLRitems($records[$i], '260', 'b')),
                    1 => implode(', ', NLRitems($records[$i], '773', 't'))
                )));
            $year = NLRitem($records[$i], '260', 'c');
            $edition = NLRitem($records[$i], '250', 'a');
            $volumeninfo = implode(', ', array_filter(array(
					0 => implode(', ', NLRitems($records[$i], '245', 'n')),
					1 => implode(', ', NLRitems($records[$i], '245', 's')),
                    2 => implode(', ', NLRitems($records[$i], '773', 'g'))
				)));
            $pages = NLRitem($records[$i], '300', 'a');
            $identifier = implode(', ',NLRitems($records[$i], '020', 'a'));
            $language = implode(', ', NLRitems($records[$i], '041', 'a'));

            $topic1 = implode(', ', NLRitems($records[$i], '650', 'a'));
            $topic2 = implode(', ', NLRitems($records[$i], '653', 'a'));
            $topic = trim(trim($topic1 . ',' . $topic2, '()'), ';');

            $udc = implode(', ', NLRitems($records[$i], '080', 'a'));
			$bbc = implode('', array_filter(array(
					0 => implode(', ', NLRitems($records[$i], '084', 'a')),
					1 => implode(', ', NLRitems($records[$i], '084', '2')),
				)));
            $series = implode('; ', array_filter(array(
					0 => implode(', ', NLRitems($records[$i], '490', 'a')),
					1 => implode(', ', NLRitems($records[$i], '490', 'v')),
					2 => implode(', ', NLRitems($records[$i], '490', 'x')),
					3 => implode(', ', NLRitems($records[$i], '440', 'a')),
					4 => implode(', ', NLRitems($records[$i], '440', 'p')),
					5 => implode(', ', NLRitems($records[$i], '440', 'n')),
					6 => implode(', ', NLRitems($records[$i], '440', 'v')),
					7 => implode(', ', NLRitems($records[$i], '440', 'x'))
				)));
			$description = implode('; ', array_filter(array(
					0 => implode(', ', NLRitems($records[$i], '505', 'a')),
					1 => implode(', ', NLRitems($records[$i], '505', 't')),
					2 => implode(', ', NLRitems($records[$i], '505', 'r')),
					3 => implode(', ', NLRitems($records[$i], '520', 'a'))
				)));

        }
    }
?>