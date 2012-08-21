<?php $this->start('slidetab-header'); ?>
	<div class="btn-slidetabRecententries">
		<i class="icon-time icon-large"></i>
	</div>
<?php $this->end('slidetab-header'); ?>
<?php $this->start('slidetab-content'); ?>
<?php if ( $CurrentUser->isLoggedIn() && $this->request->params['action'] == 'index' && $this->request->params['controller'] == 'entries' ) : ?>
		<ul class="slidetab_tree">
			<li>
				<?php
				echo __('Recent entries');
				?>
			</li>
			<?php if ( isset($recentEntries) && !empty($recentEntries) ) : ?>
				<li>
					<ul>
						<?php foreach ( $recentEntries as $entry ) : ?>
							<li>
								<i class="icon-thread"></i>
								<?php $entry['Entry']['subject'] = '' . $entry['Entry']['subject']; ?>
								<?php echo $this->EntryH->getFastLink($entry); ?><br/>
								<span class='c_info_text'>
									<?php echo $entry['User']['username']; ?>,
									<?php echo $this->Time->timeAgoInWords($entry['Entry']['time'],
											array('accuracy' => array('hour' => 'hour'))); ?>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
		</ul>
	<?php endif; ?>
<?php $this->end('slidetab-content'); ?>