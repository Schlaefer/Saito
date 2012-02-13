<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class TextHHelper extends AppHelper {

	/**
	 *
	 * @param type $string
	 * @param type $language
	 * @return type
	 * @link http://toscho.de/2009/php-funktion-genitiv/
	 */
	public function properize($string, $language = 'de') {
		$suffix = 's';
		$apo = array( 'S' => 1, 's' => 1, 'ß' => 1, 'x' => 1, 'z' =>1 );

    if (isset($apo[mb_substr($string, -1)])) {
      // Hans’ Tante
      $suffix = '’';
    } elseif ('ce' == (mb_substr($string, -2))) {
      // Alice’ Tante
      $suffix = '’';
    }

    return $string . $suffix;
		}
}
?>