<h2><?= __('Logs') ?></h2>
<p>
	<?= __(String::insert('Latest log entries. See full logs at ":path".',
		['path' => 'app/tmp/logs'])) ?>
</p>
<?php foreach($logs as $title => $content) : ?>
	<h3><?= $title . '.log' ?></h3>
	<?= $this->Admin->formatCakeLog($content) ?>
<?php endforeach; ?>
