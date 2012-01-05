<?= Stopwatch::start('slidetabs'); ?>
<div id="slidetabs">
	<?php
		if (!empty($slidetabs)) {
			foreach ($slidetabs as $slidetab) {
				echo $this->element('layout/'.$slidetab);
			}
		}
	?>
</div>
<?= Stopwatch::stop('slidetabs'); ?>