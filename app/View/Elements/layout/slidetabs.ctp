<?php Stopwatch::start('slidetabs'); ?>
<div id="slidetabs">
	<?php
		if (!empty($slidetabs)) {
			foreach ($slidetabs as $slidetab) {
				echo $this->element('layout/'.$slidetab);
			}
		}
	?>
</div>
<?php Stopwatch::stop('slidetabs'); ?>