<div>
  <?php
    if ( Configure::read('debug') == 0 ):
      echo $this->Html->script('js.min');
    else:
      echo $this->Html->script('lib/jquery-ui/jquery-ui-1.9.2.custom.min');
      echo $this->Html->script('classes/thread_line.class');
    endif;
  ?>
  <?php echo $this->fetch('script'); ?>
  <?php echo $this->Js->writeBuffer(); ?>
  <div class='clearfix'></div>
  <?php
		if ($showStopwatchOutput) {
			echo 'Peak memory usage: ' . $this->Number->toReadableSize(memory_get_peak_usage()) . "<br/>";
			echo $this->Html->tag('div', $this->Stopwatch->getResult(), array('style' => 'float: left;'));
			echo $this->Html->tag('div', $this->Stopwatch->plot(), array('style' => 'float: left; margin-left: 2em;'));
		}
  ?>
<?php echo $this->element('sql_dump'); ?>
</div>