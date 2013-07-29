<?php

	App::uses('AppHelper', 'View/Helper');

	class SettingHelper extends AppHelper {

		protected $_headers = [];

		public $helpers = [
			'Html'
		];

		public function table($table_name, array $setting_names, $Settings, array $options = []) {
			$defaults = [
				'nav-title' => $table_name
			];
			$options += $defaults;

			$out = $this->tableHeaders();
			foreach ($setting_names as $name) {
				$out .= $this->tableRow($name, $Settings);
			}
			$key = $this->addHeader($options['nav-title']);
			$out = '<table class="table table-striped table-bordered table-condensed">'
					. $out
					. '</table>';
			$out = '<div id="navHeaderAnchor' . $key . '"></div>'
					. '<h2>' . $table_name . '</h2>'
					. $out;
			return $out;
		}

		public function addHeader($header) {
			$id = count($this->_headers) + 1;
			$this->_headers[$id] = $header;
			return $id;
		}

		public function getHeaders() {
			return $this->_headers;
		}

		public function tableRow($name, $Settings) {
			return $this->Html->tableCells(
				[
					__($name),
					$Settings[$name],
					"<p>" . __($name . '_exp') . "</p>",
					$this->Html->link(
						__('edit'),
						['controller' => 'settings', 'action' => 'edit', $name],
						['class' => 'btn']
					)
				]
			);
		}

		public function tableHeaders() {
			return $this->Html->tableHeaders(
				[
					__('Key'),
					__('Value'),
					__('Explanation'),
					__('Actions')
				]
			);
		}
	}
