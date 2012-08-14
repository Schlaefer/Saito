<?php

	App::uses('AppHelper', 'View/Helper');
	App::import('Lib', 'Stopwatch.Stopwatch');

	class StopwatchHelper extends AppHelper {

		public $helpers = array(
				'Html'
		);

		public function plot() {
			$stopwatch_data = Stopwatch::getJs();
			$out = $this->Html->script(
					array(
						'http://cdnjs.cloudflare.com/ajax/libs/flot/0.7/jquery.flot.min.js',
						'http://cdnjs.cloudflare.com/ajax/libs/flot/0.7/jquery.flot.stack.min.js',
							)
			);
			$out .= '<div id="stopwatch-plot" style="width:300px;height:600px;"></div>';
			$out .= '<div style="width: 300px;"><div id="stopwatch-exp">---</div><div id="stopwatch-legend"></div></div>';
			$out .= $this->Html->scriptBlock(<<<EOF
		$.plot($("#stopwatch-plot"), {$stopwatch_data}, {
				series: {
						stack: true,
						lines: {show: false, steps: false },
						bars: {
								show: true,
								barWidth: 0.8,
								align: 'center',
								lineWidth: 0
						}
				},
				legend: {
						show: false,
						container: $('#stopwatch-legend')
				},
				grid: {
						hoverable: true
				},
				xaxis: {
						ticks: [
								[1,'wdiff'],
								[2,'udiff']
						]
				},
		});
		$("#stopwatch-plot").bind("plothover", function (event, pos, item) {
        if (item) {
					var y = (item.datapoint[1] - item.datapoint[2]).toFixed(3);
					$("#stopwatch-exp").html(y + " ms " + item.series.label);
					$(".stopwatch-row").css('color', 'inherit');
					$("#stopwatch-" + (item.seriesIndex + 1)).css('color', 'red');
        }
    });
EOF
				);
			return $out;
		}

		public function getResult() {
			return "<pre>" . Stopwatch::getString() . "</pre>";
		}

	}

