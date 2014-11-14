<?php

	App::uses('AppHelper', 'View/Helper');

	class FlotHelper extends AppHelper {

		public $helpers = ['Html'];

		public function plot($title, $data) {
			$options = [
					'xaxis' => ['tickDecimals' => 0],
					'yaxis' => ['min' => 0]
			];
			return $this->_plot($title, $data, $options);
		}

		public function plotDouble($title, $data) {
			$options = [
					'xaxis' => ['mode' => 'time', 'timeformat' => '%y'],
					'yaxes' => [
							new ArrayObject(), // create empty JSON object '{}'
							['alignTicksWithAxis' => 1, 'position' => 'right']
					],
					'legend' => ['position' => 'nw'],
					'series' => ['lines' => ['steps' => true]]
			];
			return $this->_plot($title, $data, $options);
		}

		protected function _plot($title, $data, $options) {
			$id = $this->Html->tagId();
			$options = json_encode($options);

			$plot = $this->Html->tag('h2', $title);

			if (empty($data)) {
				$plot .= $this->Html->para(null, __('admin.stats.insfd'));
				return $plot;
			}

			// wrap single dataset for flot if not already done
			if (isset($data['data'])) {
				$data = [$data];
			}

			$data = json_encode($data);

			$plot .= $this->Html->div('admin-stats-plot', '', ['id' => $id]);
			$plot .= $this->Html->scriptBlock("$.plot('#$id', $data, $options);");

			return $plot;
		}

	}