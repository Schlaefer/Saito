<?php

	App::uses('BbcodeProcessor', 'Lib/Bbcode/Processors');

	class BbcodePreparePreprocessor extends BbcodeProcessor {

		public function process($string) {
			$string = h($string);
			// Consolidates '\n\r', '\r' to `\n`
			// @todo @bogus same string twice in regex?
			$string = preg_replace('/\015\012|\015|\012/', "\n", $string);
			return $string;
		}

	}