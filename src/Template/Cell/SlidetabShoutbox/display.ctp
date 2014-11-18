<?php
	echo $this->Html->scriptBlock('SaitoApp.shouts = ' . json_encode($this->Shouts->prepare($shouts)));
	?>
<?php $this->start('slidetab-tab-button'); ?>
<div class="btn-slidetabShoutbox">
	<i class="fa fa-comments-o fa-lg"></i>
</div>
<?php $this->end('slidetab-tab-button'); ?>
<?php $this->start('slidetab-content'); ?>
<div id='shoutbox'></div>
<?php $this->end('slidetab-content'); ?>
<?= $this->element('layout/slidetabs'); ?>
