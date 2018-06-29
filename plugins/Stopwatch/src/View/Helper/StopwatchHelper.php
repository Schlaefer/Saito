<?php
declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Stopwatch\View\Helper;

use Cake\View\Helper;
use Cake\View\Helper\HtmlHelper;
use Cake\View\Helper\NumberHelper;
use Stopwatch\Lib\Stopwatch;

/**
 * Stopwatch Helper
 *
 * @property HtmlHelper $Html
 * @property NumberHelper $Number
 */
class StopwatchHelper extends Helper
{
    public $helpers = [
        'Html',
        'Number'
    ];

    /**
     * Renders Stopwatch Html results
     *
     * @return string
     */
    public function html(): string
    {
        $memory = 'Peak memory usage: ' . $this->Number->toReadableSize(memory_get_peak_usage());
        $html = $this->Html->para('', $memory);

        $html .= $this->Html->tag(
            'div',
            $this->getResult(),
            ['style' => 'float: left;']
        );
        $html .= $this->Html->tag(
            'div',
            $this->plot(),
            ['style' => 'float: left; margin-left: 2em;']
        );

        return $this->Html->div('stopwatch-debug', $html);
    }

    /**
     * Get plot
     *
     * @return string
     */
    private function plot()
    {
        $stopwatchData = Stopwatch::getJs();
        $out = $this->Html->script(
            [
                'Stopwatch.jquery.flot.min.js',
                'Stopwatch.jquery.flot.stack.min.js'
            ]
        );
        $out .= '<div id="stopwatch-plot" style="width:300px;height:600px;"></div>';
        $out .= '<div style="width: 300px;"><div id="stopwatch-exp">---</div><div id="stopwatch-legend"></div></div>';
        $out .= $this->Html->scriptBlock(
            <<<EOF
            		$.plot($("#stopwatch-plot"), {$stopwatchData}, {
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

    /**
     * Get result
     *
     * @return string
     */
    private function getResult()
    {
        return "<pre>" . Stopwatch::getString() . "</pre>";
    }
}
