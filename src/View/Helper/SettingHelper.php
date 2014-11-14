<?php

	App::uses('AppHelper', 'View/Helper');

	class SettingHelper extends AppHelper {

		protected $_headers = [];

		public $helpers = [
			'Html',
			'SaitoHelp'
		];

		public function table($tableName, array $settingNames, $Settings, array $options = []) {
			$defaults = [
				'nav-title' => $tableName
			];
			$options += $defaults;

			$out = $this->tableHeaders();
			$anchors = '';
			foreach ($settingNames as $name) {
				$out .= $this->tableRow($name, $Settings);
				$anchors .= '<a name="' . $name . '"></a>';
			}
			$key = $this->addHeader($options['nav-title']);
			$out = '<table class="table table-striped table-bordered table-condensed">' .
					$out . '</table>';

			$sh = '';
			if (!empty($options['sh'])) {
				$sh = $this->SaitoHelp->icon($options['sh'],
					['style' => 'float: right; margin: 1em']);
			}

			$out = '<div id="navHeaderAnchor' . $key . '"></div>' .
					$sh .
					$anchors .
					'<h2 >' . $tableName . '</h2>' .
					$out;
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
					__d('nondynamic', $name),
					$Settings[$name],
					"<p>" . __d('nondynamic', $name . '_exp') . "</p>",
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
