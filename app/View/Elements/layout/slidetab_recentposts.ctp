<?php $this->start('slidetab-header'); ?>
	<div class="btn-slidetabRecentposts">
		<i class="fa fa-book fa-lg"></i>
	</div>
<?php $this->end('slidetab-header'); ?>
<?php $this->start('slidetab-content'); ?>
	<ul class="slidetab_tree">
		<li>
			<span title='The sea was angry that day my friends, like an old man trying to send back soup in a deli …'>
				<?php
					// @lo
					echo $this->TextH->properize($CurrentUser['username'])
							. ' ' . __('user_recentposts');
				?>
			</span>
		</li>
		<?php if (!empty($recentPosts)) : ?>
		<li>
			<ul>
				<?php foreach ($recentPosts as $entry) : ?>
					<li>
						<i class="fa fa-thread"></i>
						<?= $this->EntryH->getFastLink($entry) ?>
						<br/>
							<span class='c_info_text'>
								<?php echo $this->TimeH->formatTime($entry['Entry']['time']); ?>
							</span>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
	</ul>
<?php $this->end('slidetab-content'); ?>