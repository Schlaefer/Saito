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
			$url = '';
			$name = 'jquery';
			if ((int)Configure::read('debug') === 0) {
				$name = '../dist/' . $name . '.min';
			} else {
				$url = '../dev/bower_components/jquery/';
			}
			return $this->Html->script($url . $name);
		}
	}
