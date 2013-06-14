<?php $this->start('slidetab-header'); ?>
	<div class="btn-slidetabRecententries">
		<i class="icon-time icon-large"></i>
	</div>
<?php $this->end('slidetab-header'); ?>
<?php $this->start('slidetab-content'); ?>
	<ul class="slidetab_tree">
		<li>
			<?= __('Recent entries') ?>
		</li>
		<?php if (!empty($recentEntries)) : ?>
			<li>
				<ul>
					<?php foreach ($recentEntries as $entry) : ?>
						<li>
							<i class="icon-thread"></i>
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
	</ul>
<?php $this->end('slidetab-content'); ?>