<?= Stopwatch::start('entries/mix'); ?>
<div id="entry_mix" class="entry mix" style="position:relative;">
	<?php
		echo $this->Html->link('<div class="btn_back_l">&nbsp;</div>', array( 'action' => 'index', 'jump' => $entries[0]['Entry']['id'] ), array('escape' => false));
	?>
	<div style="margin-left: 25px;">
		<?php echo $this->element('entry/mix', array ( 'entry_sub' => $entries[0], 'level' => 0 )) ; ?>
	</div>
</div>
<?= Stopwatch::stop('entries/mix');?>