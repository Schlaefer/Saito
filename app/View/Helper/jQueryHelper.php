<?php

	App::uses('AppHelper', 'View/Helper');

	/**
	 * Insert tag for including jQuery
	 *
	 * Allows to easily change jQuery version in all layouts
	 */
	class jQueryHelper extends AppHelper {

		public $helpers = array(
			'Html'
		);

		public $jQueryVersion =  "1.9.1";

		public function scriptTag() {
			$name = "lib/jquery/jquery-" . $this->jQueryVersion;
			if ((int)Configure::read('debug') === 0) {
				$name .= '.min' ;
			}
			return $this->Html->script($name);
		}

	}
