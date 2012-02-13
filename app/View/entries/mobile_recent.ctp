<?php
/**
 * View setup
 */
$lastHour = 0;
$hourTreshold = time() - 3600;
$threeHourTreshold = time() - (3 * 3600);
?>

<div data-role="page" data-add-back-btn="false">

	<div data-role="header">
		<h1><?php echo $title_for_layout ?></h1>
		<?php
		echo $this->Html->link(
				'Refresh', '/mobile/entries/recent/setAsRead:1',
				array(
					'class' 				=> 'ui-btn-right',
					'data-icon' 		=> "refresh",
					'data-iconpos'	=> 'notext',
					'data-ajax' 		=> 'false',
					'escape' 				=> false,
				)
		);
		?>
	</div><!-- /header -->

	<div data-role="content"  class="ui-scrolllistview">
		<ul id="recentEntries" data-role="listview" >
			<?php foreach ( $recentEntries as $entry ): ?>
				<?php if ( strtotime($entry['Entry']['time']) > $hourTreshold && $lastHour === 0): ?>
					<li data-role="list-divider" >
						In der letzten Stunde
					</li>
					<?php $lastHour = 1; ?>
				<?php elseif ( strtotime($entry['Entry']['time']) < $hourTreshold AND strtotime($entry['Entry']['time']) > $threeHourTreshold && $lastHour <= 1) : ?>
					<li data-role="list-divider">
						Älter als eine Stunde
					</li>
					<?php $lastHour = 2; ?>
				<?php elseif ( strtotime($entry['Entry']['time']) < $threeHourTreshold && $lastHour <= 1) : ?>
					<li data-role="list-divider">
						Älter als drei Stunden
					</li>
					<?php $lastHour = 3; ?>
				<?php endif; ?>
				<li <?php
					if ( $this->EntryH->isNewEntry($entry, $CurrentUser) ):
							echo 'data-theme="b"';
					endif;
				?>>
					<?php
						$href = "{$this->request->webroot}mobile/entries/mix/{$entry['Entry']['tid']}";
						if ( $entry['Entry']['id'] != $entry['Entry']['tid'] ):
							$href .= "/jump:{$entry['Entry']['id']}";
						endif;
					?>
					<a href="<?php echo $href; ?>">
						<h3 class-="ui-li-heading">
							<?php
							echo $this->EntryH->getSubject($entry);
							?>
						</h3>
						<p class="ui-li-desc">
							<strong>
								<?php echo $entry['User']['username'] ?> – <?php echo $this->TimeH->formatTime($entry['Entry']['time']); ?>
							</strong>
						</p>
						<p class="ui-li-desc">
							<?php echo $this->Text->truncate($entry['Entry']['text'],
									50); ?>
						</p>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div><!-- /content -->