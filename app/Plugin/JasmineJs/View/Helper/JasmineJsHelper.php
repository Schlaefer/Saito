<?php

	App::uses('AppHelper', 'View/Helper');

	class JasmineJsHelper extends AppHelper {

		public $helpers = array(
			'Html'
		);

		protected  $_files = array(
			'js' => array(
				'JasmineJs.jasmine.js',
				'JasmineJs.jasmine-html.js',
				'JasmineJs.jasmine-jquery.js',
				'JasmineJs.sinon-1.6.0.js'
			),
			'css' => array(
				'JasmineJs.jasmine.css'
			)
		);

		public function beforeRender($viewFile) {
			$this->Html->script(
				$this->_files['js'],
				array(
					'block' => 'JasmineJs'
				)
			);
			$this->Html->css(
				$this->_files['css'],
				null,
				array(
					'block' => 'JasmineJs'
				)
			);
		}
	}
