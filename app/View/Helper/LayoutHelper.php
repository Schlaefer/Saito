<?php

	App::uses('AppHelper', 'View/Helper');

	class LayoutHelper extends AppHelper {

		public $helpers = [
			'Html'
		];

		public $jQueryVersion = "2.0.2";

		public function beforeLayout($layoutFile) {
			$stylesheets =
					[
						'stylesheets/static.css',
						'stylesheets/styles.css'
					];
			if (Configure::read('debug')) {
				$stylesheets[] = 'stylesheets/cake.css';
			}
			$this->Html->css($stylesheets, null, ['inline' => false]);
		}

		public function jQueryTag() {
			$name = "lib/jquery/jquery-" . $this->jQueryVersion;
			if ((int)Configure::read('debug') === 0) {
				$name .= '.min';
			}

			return $this->Html->script($name);
		}
	}
