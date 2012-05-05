<?php echo  Stopwatch::start('entries/mix'); ?>

<?php
  $this->start('headerSubnavLeft');
    echo $this->Html->link(
        '<i class="icon-arrow-left"></i> ' . __('Back'),
        $this->EntryH->getPaginatedIndexPageId($entries[0]['Entry']['tid'], $lastAction),
        array( 'class' => 'textlink', 'escape' => FALSE )
        );
  $this->end();
?>

<div id="entry_mix" class="entry mix" style="position:relative;">
	<?php
		echo $this->Html->link(
        '<div class="btn-strip btn-strip-back">&nbsp;</div>',
        $this->EntryH->getPaginatedIndexPageId($entries[0]['Entry']['tid'], $lastAction),
        array('escape' => FALSE)
        );
	?>
	<div style="margin-left: 25px;">
		<?php echo $this->element('entry/mix', array ( 'entry_sub' => $entries[0], 'level' => 0 )) ; ?>
	</div>
</div>
<?php echo  Stopwatch::stop('entries/mix');?>