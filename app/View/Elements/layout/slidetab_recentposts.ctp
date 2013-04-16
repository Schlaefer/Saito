<?php $this->start('slidetab-header'); ?>
	<div class="btn-slidetabRecentposts">
		<i class="icon-book icon-large"></i>
	</div>
<?php $this->end('slidetab-header'); ?>
<?php $this->start('slidetab-content'); ?>
	<ul class="slidetab_tree">
		<li>
			<span title='The sea was angry that day my friends, like an old man trying to send back soup in a deli …'>
				<?php
					// @lo
				echo $this->TextH->properize($CurrentUser['username']) . ' ' . __('user_recentposts');
				?>
			</span>
		</li>
				<?php if ( isset($recentPosts) && !empty($recentPosts) ) : ?>
			<li>
				<ul>
						<?php foreach ( $recentPosts as $entry ) : ?>
						<li>
							<i class="icon-thread"></i>
							<?php
//									if ( strlen($entry['Entry']['subject']) > 20 ) {
//										$s = html_entity_decode($entry['Entry']['subject'], ENT_QUOTES);
//										$sub = mb_substr($s, 0, 20);
//										$entry['Entry']['subject'] = htmlentities($sub);// . '…';
//										}
							$entry['Entry']['subject'] = '' . $entry['Entry']['subject'];
							?>
						<?php echo $this->EntryH->getFastLink($entry); ?><br/> <span class='c_info_text'><?php echo $this->TimeH->formatTime($entry['Entry']['time']); ?></span>
						</li>
				<?php endforeach; ?>
				</ul>
	<?php endif; ?>
	</ul>
<?php $this->end('slidetab-content'); ?>