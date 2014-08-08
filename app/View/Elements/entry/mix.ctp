<?php
	$_et = $this->EntryH->generateEntryTypeCss(
			$level,
      !$CurrentUser->ReadEntries->isRead($entry_sub['Entry']['id'], $entry_sub['Entry']['time']),
			$entry_sub['Entry']['id'],
			($this->request->params['action'] === 'view') ? $entry_sub['Entry']['id'] : null
	);
?>
<?php if ($level < Configure::read('Saito.Settings.thread_depth_indent')) : ?>
	<ul class="threadTree-node<?php echo ($level === 0) ? ' root' : ''; ?>">
<?php endif; ?>
	<li id="<?= $entry_sub['Entry']['id'] ?>"
			class="<?= $_et ?>" style="">
		<div class="mixEntry panel">
			<?=
				$this->element('/entry/view_posting',
						[
								'entry' => $entry_sub,
								'level' => $level,
						])
			?>
		</div>
	</li>
	<?php if (isset($entry_sub['_children'])): ?>
		<li>
			<?php
				foreach ($entry_sub['_children'] as $child) {
					echo $this->element('entry/mix',
							['entry_sub' => $child, 'level' => $level + 1]);
				}
			?>
		</li>
	<?php endif ?>
<?php if ($level < Configure::read('Saito.Settings.thread_depth_indent')): ?>
	</ul>
<?php endif; ?>