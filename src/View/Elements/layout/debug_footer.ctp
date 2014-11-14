<div id="debugFooter">
	<?php
		if ($this->get('showStopwatchOutput')) {
		echo 'Peak memory usage: ' . $this->Number->toReadableSize(memory_get_peak_usage()) . "<br/>";
		echo $this->Html->tag('div', $this->Stopwatch->getResult(), array('style' => 'float: left;'));
		echo $this->Html->tag('div', $this->Stopwatch->plot(), array('style' => 'float: left; margin-left: 2em;'));
		}
		echo $this->element('sql_dump');
	?>
</div>
