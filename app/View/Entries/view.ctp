<?php echo  Stopwatch::start('view.ctp'); ?>
<?php if (!isset($level)) $level = 0; ?>
<?
	if ($show_answer) {
		$this->Html->scriptBlock("$(window).load(function() { $('#forum_answer_".$entry["Entry"]['id']."').trigger('click'); });", array('inline' => false ));
	}
?>
<div id="entry_view" class="entry view">
	<div class="a">
				<?php  echo $this->element('/entry/view_posting', array('entry' => $entry, 'level' => $level,)); # 'cache' => array('key' => $entry["Entry"]['id'], 'time' => '+1 day'))); ?>
	</div> <!-- a -->

	<div class="thread_tools" style='opacity: 1;'>
	<ul>
			<li>
				<a href="<?php echo $this->request->webroot;?>/entries/mix/<?php echo $entry["Entry"]['tid']; ?>#<?php echo $entry['Entry']['id'];?>" id="btn_show_mix_<?php echo $entry['Entry']['tid']; ?>"><span class="img_mix"></span></a>
			</li>
		</ul>
	</div> <!-- thread_tools -->
	<div class="b">
		<p>
			<strong>
				<?php echo  __('whole_thread_marking');	?>:
			</strong>
		</p>
		<?php echo  $this->element('entry/thread_cached_init', array ( 'entries_sub' => $tree, 'level' => 0)); ?>
	</div> <!-- b -->
</div>

<?php echo  Stopwatch::stop('view.ctp'); ?>
