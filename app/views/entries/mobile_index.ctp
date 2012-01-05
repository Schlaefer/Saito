<div data-role="page" data-add-back-btn="false">

	<div data-role="header">
		<h1><?php echo $title_for_layout ?></h1>
		<?php
		echo $this->Html->link(
				'Refresh', '/mobile/entries/index/setAsRead:1',
				array(
					'class' => 'ui-btn-right',
					'data-icon' => "refresh",
					'data-ajax' => 'false',
					'data-iconpos'	=> 'notext',
					'escape' => false,
				)
		);
		?>
	</div><!-- /header -->

	<div data-role="content" data-theme="b">
		<ul data-role="listview">

			<?php foreach ( $entries as $entry ): ?>
				<li <?php
					if ( $entry['pid'] == 0 AND $this->EntryH->hasNewEntries( array( 'Entry' => $entry ), $CurrentUser ) ) echo 'data-theme="b"';
				?>>
					<?php
					if ( $entry['fixed'] == TRUE ):
						?>
						<img src="<?php echo $this->webroot . 'theme' . DS . $this->theme . DS ?>img/mobile/fixed.png" class='ui-li-icon' />
						<?php
					endif;
					?>
					<?php echo $this->Html->link($entry['subject'],
							array( 'action' => 'mix', $entry['tid'] ), array( 'escape' => false )); ?>
				</li>
<?php endforeach; ?>
		</ul>
	</div>