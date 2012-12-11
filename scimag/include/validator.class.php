<?php


class validate {

	var $text_status = false;

	function is_one_word ($input, $min_size=false) {
		$status = false;
		if ($min_size) {
			if (strlen($input) >= $min_size) {
				if (ctype_alnum($input)) {
					$status = true;
				}
			}
		}
		else {
			if (ctype_alnum($input) && strlen($input) > 0) {
				$status = true;
			}
		}
		return $status;
	}

	function is_digit($input, $decimal=false) {
		$status = false;
		if ($decimal) {
			if (eregi(',')) {
				$tmp_input = explode(',', $input);
			}
			else {
				$tmp_input = explode('.', $input);
			}

			for ($i=0; $i<count($tmp_input); $i++) {
				if (ctype_digit($tmp_input[$i])) {
					$status = true;
				}
			}
		}
		else {
			if (ctype_digit($input)){
				$status = true;
			}
		}
		return $status;
	}

	function is_email ($input) {
		$status = false;
		if (eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $input)) {
			$status = true;
		}
		return $status;
	}

	function is_url( $urladdr,$return = false ) {
		$regexp = "^(https?://)?(([0-9a-z_!~*'().&=+$%-]+:)?[0-9a-z_!~*'().&=+$%-]+@)?((([12]?[0-9]{1,2}\.){3}[12]?[0-9]{1,2})|(([0-9a-z_!~*'()-]+\.)*([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.(com|net|org|edu|mil|gov|int|aero|coop|museum|name|info|biz|pro|[a-z]{2})))(:[1-6]?[0-9]{1,4})?((/?)|(/[0-9a-z_!~*'().;?:@&=+$,%#-]+)+/?)$";

		if (eregi( $regexp, $urladdr )){
			if (!eregi( "^https?://", $urladdr )) $urladdr = "http://" . $urladdr;

			if (!eregi( "^https?://.+/", $urladdr )) $urladdr .= "/";
			if ((eregi( "/[0-9a-z~_-]+$", $urladdr)) && (!eregi( "[\?;&=+\$,#]", $urladdr))) $urladdr .= "/";
			if ($return) return  $urladdr; else return true;
		}
		else {
			return false;
		}

	}


	function is_valid_text ($input, $return=false, $safe=false) {
		$text = false;
		if ($return == true) {
			if (strlen(trim($input)) > 0) {
				if ($safe) {
					$text = strip_tags($input);
					$text = htmlspecialchars($text);
				}
				else {
					if (!get_magic_quotes_gpc()) {
						$text = addslashes($input);
					}
					else {
						$text = $input;
					}
				}
			}
		}
		else {
			if (strlen(trim($input)) > 0) {
				$text = true;
			}
		}
		return $text;
	}

}

?>