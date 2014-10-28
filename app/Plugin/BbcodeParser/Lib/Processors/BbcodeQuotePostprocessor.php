<?php

	App::uses('BbcodeProcessor', 'BbcodeParser.Lib/Processors');

	class BbcodeQuotePostprocessor extends BbcodeProcessor {

		public function process($string) {
			$quoteSymbolSanitized = h($this->_sOptions['quote_symbol']);
			$string = preg_replace(
			// Begin of the text or a new line in the text, maybe one space afterwards
				'/(^|\n\r\s?)' .
				$quoteSymbolSanitized .
				'\s(.*)(?!\<br)/m',
				"\\1<span class=\"richtext-citation\">" . $quoteSymbolSanitized . " \\2</span><br>",
				$string
			);
			return $string;
		}

	}
