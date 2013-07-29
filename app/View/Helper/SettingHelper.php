<?php

	App::uses('AppHelper', 'View/Helper');

	class SettingHelper extends AppHelper {

		public $helpers = [
			'Html'
		];

		public function table($table_name, array $setting_names, $Settings) {
			$out = $this->tableHeaders();
			foreach ($setting_names as $name) {
				$out .= $this->tableRow($name, $Settings);
			}
			$out = '<table class="table table-striped table-bordered table-condensed">'
					. $out
					. '</table>';
			$out = '<h2>' . $table_name . '</h2>' . $out;
			return $out;
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
