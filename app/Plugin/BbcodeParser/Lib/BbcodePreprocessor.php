<?php

	App::uses('SaitoMarkupPreprocessor', 'Lib/Saito/Markup');

	class BbcodePreprocessor extends SaitoMarkupPreprocessor {

		public function process($string) {
			return $this->_hashInternalEntryLinks($string);
		}

		protected function _hashInternalEntryLinks($string) {
			$string = preg_replace(
				"%
				(?<!=) # don't hash if part of [url=…
				{$this->_settings->get('server')}{$this->_settings->get('webroot')}{$this->_settings->get('hashBaseUrl')}
				(\d+)  # the id
				%imx",
				"#\\1",
				$string
			);
			return $string;
		}

	}