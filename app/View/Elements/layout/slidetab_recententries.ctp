<?php $this->start('slidetab-tab-button'); ?>
	<div class="btn-slidetabRecententries">
		<i class="fa fa-clock-o fa-lg"></i>
	</div>
<?php $this->end('slidetab-tab-button'); ?>
<?php $this->start('slidetab-content'); ?>
	<div class="slidetab-header">
		<h4>
			<?= __('Recent entries') ?>
		</h4>
	</div>
	<?php if (!empty($recentEntries)) : ?>
		<div class="slidetab-content">
			<ul class="slidetab_tree">
				<?php foreach ($recentEntries as $entry) : ?>
					<li>
						<i class="fa fa-thread"></i>
						<?= $this->EntryH->getFastLink($entry); ?>
						<br/>
								<span class='c_info_text'>
									<?= h($entry['User']['username']) ?>,
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
		</div>
	<?php endif; ?>
<?php $this->end('slidetab-content'); ?>