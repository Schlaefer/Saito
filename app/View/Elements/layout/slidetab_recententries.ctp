<?php $this->start('slidetab-header'); ?>
	<div class="btn-slidetabRecententries">
		<i class="fa fa-clock-o fa-lg"></i>
	</div>
<?php $this->end('slidetab-header'); ?>
<?php $this->start('slidetab-content'); ?>
	<h4>
		<?= __('Recent entries') ?>
	</h4>
	<?php if (!empty($recentEntries)) : ?>
	<ul class="slidetab_tree">
		<?php foreach ($recentEntries as $entry) : ?>
			<li>
				<i class="fa fa-thread"></i>
				<?= $this->EntryH->getFastLink($entry); ?>
				<br/>
							<span class='c_info_text'>
								<?= $entry['User']['username']; ?>,
								<?=
									$this->Time->timeAgoInWords(
										$entry['Entry']['time'],
										[
											'accuracy' => ['hour' => 'hour']
										]
									); ?>
							</span>
			</li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>
<?php $this->end('slidetab-content'); ?>