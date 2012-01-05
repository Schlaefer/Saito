<?= Stopwatch::start('view.ctp'); ?>
<?php if (!isset($level)) $level = 0; ?>
<?
	if ($show_answer) {
		$html->scriptBlock("$(window).load(function() { $('#forum_answer_".$entry["Entry"]['id']."').trigger('click'); });", array('inline' => false ));
	}
?>
<div id="entry_view" class="entry view">
	<div class="a">
				<? echo $this->element('/entry/view_posting', array('entry' => $entry, 'level' => $level,)); # 'cache' => array('key' => $entry["Entry"]['id'], 'time' => '+1 day'))); ?>
	</div> <!-- a -->

	<div class="thread_tools" style='opacity: 1;'>
	<ul>
			<li>
				<a href="<?php echo $this->webroot;?>/entries/mix/<?php echo $entry["Entry"]['tid']; ?>#<?php echo $entry['Entry']['id'];?>" id="btn_show_mix_<?php echo $entry['Entry']['tid']; ?>"><span class="img_mix"></span></a>
			</li>
		</ul>
	</div> <!-- thread_tools -->
	<div class="b">
		<p>
			<strong>
				<?= __('whole_thread_marking');	?>:
			</strong>
		</p>
		<?= $this->element('entry/thread_cached_init', array ( 'entries_sub' => $tree, 'level' => 0)); ?>
	</div> <!-- b -->
</div>

<?= Stopwatch::stop('view.ctp'); ?>
