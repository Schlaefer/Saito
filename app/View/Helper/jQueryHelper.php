<?php

	App::uses('AppHelper', 'View/Helper');

	class jQueryHelper extends AppHelper {

		public $helpers = array(
			'Html'
		);

		public $jQueryVersion =  "1.9.0";

		public function scriptTag() {
			$name = "lib/jquery/jquery-" . $this->jQueryVersion;
			if ((int)Configure::read('debug') === 0) {
				$name .= '.min' ;
			}
			return $this->Html->script($name);
		}

	}
