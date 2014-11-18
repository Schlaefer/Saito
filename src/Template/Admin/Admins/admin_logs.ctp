<h2><?= __('Logs') ?></h2>
<p>
	<?= __('Latest log entries. See full logs at "%s".', ['app/tmp/logs']) ?>
</p>
<?php
	if (empty($logs)) {
		return;
	}
	foreach($logs as $title => $content) {
		echo $this->Html->tag('h3', $title);
		echo $this->Admin->formatCakeLog($content) ;
	}
