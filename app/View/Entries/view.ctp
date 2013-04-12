<?php
	Stopwatch::start('view.ctp');

  $this->start('headerSubnavLeft');
    echo $this->Html->link(
        '<i class="icon-arrow-left"></i> ' . __('back_to_forum_linkname'),
        $this->EntryH->getPaginatedIndexPageId($entry['Entry']['tid'], $lastAction),
					array(
						'class' => 'textlink',
						'escape' => FALSE,
						'rel' => 'nofollow',
					)
        );
  $this->end();

	$this->append('meta');
	if (empty($entry['Entry']['text'])) {
		echo $this->Html->tag(
			'meta',
			null,
			['name' => 'description', 'content' => $entry['Entry']['subject']]
		);
	}
	$this->end('meta');

	if (isset($level) === false) { $level = 0; }
	if ($show_answer) {
		$this->Html->scriptBlock("$(window).load(function() { $('#forum_answer_".$entry["Entry"]['id']."').trigger('click'); });", array('inline' => false ));
	}
?>
<div id="entry_view" class="entry view">
	<div class="box-content">
				<?php  echo $this->element('/entry/view_posting', array('entry' => $entry, 'level' => $level,)); # 'cache' => array('key' => $entry["Entry"]['id'], 'time' => '+1 day'))); ?>
	</div>
	<?php echo $this->element(
			'entry/thread_cached_init',
			array (
					'entries_sub' => $tree,
					'level' => 0,
					'toolboxButtons' => ['mix' => true]
					)
			); ?>
</div>

<?php Stopwatch::stop('view.ctp'); ?>
