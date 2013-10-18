<?php

	App::uses('AppHelper', 'View/Helper');

	class LayoutHelper extends AppHelper {

		public $helpers = [
			'Html'
		];

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
			$url = '../dist/';
			$name = 'jquery';
			if ((int)Configure::read('debug') === 0) {
				$name = $name . '.min';
			}
			return $this->Html->script($url . $name);
		}
	}
