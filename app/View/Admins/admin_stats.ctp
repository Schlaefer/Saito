<?php
	echo $this->Html->script(
			array(
					'lib/flot/jquery.flot.min.js',
			)
		);
	?>
<h1> <?php echo __('Stats'); ?> </h1>
<h2> <?php echo __('Entries'); ?> </h2>

<div id="plot-entries" class="admin-stats-plot"></div>

	<?php
		echo $this->Html->scriptBlock(<<<EOF
var plot = function(el, data) {
	$.plot(
		$(el),
		data,
		{
			xaxis: {
				mode: "time",
				timeformat: "%y/%m"
			},
			yaxes: [ {},
							{
								alignTicksWithAxis: 1,
								position: 'right'
							} ],
			legend: {
				position: 'nw'
			},
			series: {
				lines: {
					steps: true
				}
			}
			}
		);
	};

	plot("#plot-entries", {$entries});
EOF
		);
	?>
<h2> <?php echo __('User Registrations'); ?> </h2>

<div id="plot-user-registration" class="admin-stats-plot"></div>

	<?php
		echo $this->Html->scriptBlock(<<<EOF
	plot("#plot-user-registration", {$user_registrations});
EOF
		);
	?>