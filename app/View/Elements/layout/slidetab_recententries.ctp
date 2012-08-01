<?php // Stopwatch::start('slidetab_recententries'); ?>
<?php if ( $CurrentUser->isLoggedIn() && $this->request->params['action'] == 'index' && $this->request->params['controller'] == 'entries' ) : ?>
		<?php
		echo $this->element('layout/slidetabs__header',
				array(
				'id' => 'recententries',
				'btn_class' => 'btn-slidetabRecententries',
				'btn_content' => '<i class="icon-time icon-large"></i>',
				)
		);
		?>
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
									<?php echo $this->Time->timeAgoInWords($entry['Entry']['time']); ?>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
		</ul>
		<?php echo $this->element('layout/slidetabs__footer'); ?>
	<?php endif; ?>
<?php // Stopwatch::end('slidetab_recententries'); ?>