<h2><?= __('Logs') ?></h2>
<p>
	<?= __('Latest log entries. See full logs at "%s".', ['app/tmp/logs']) ?>
</p>
<?php foreach($logs as $title => $content) : ?>
	<h3><?= $title . '.log' ?></h3>
	<?= $this->Admin->formatCakeLog($content) ?>
<?php endforeach; ?>
