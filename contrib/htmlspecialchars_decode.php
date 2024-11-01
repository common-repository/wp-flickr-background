<?php
/*
The following code was provided by Thomas
http://uk.php.net/manual/en/function.htmlspecialchars-decode.php#82133
*/

if (!function_exists('htmlspecialchars_decode')) {
	function htmlspecialchars_decode($string, $style=ENT_COMPAT) {
		$translation = array_flip(get_html_translation_table(HTML_SPECIALCHARS, $style));
		if($style === ENT_QUOTES){ $translation['&#039;'] = '\''; }
		return strtr($string, $translation);
	}
}