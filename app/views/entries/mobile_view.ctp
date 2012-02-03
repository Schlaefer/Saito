<div data-role="page" data-add-back-btn="true">

	<div data-role="header">
		<h1><?php echo $title_for_layout ?></h1>
		<?php
		echo $this->Html->link(
				'Mix',
				'/mobile/entries/mix/' . $entry['Entry']['tid'] . '#' . $entry['Entry']['id'],
				array(
					'class' => 'ui-btn-right',
					'data-icon' => "arrow-r",
//					'data-ajax' => 'false',
				)
		);
		?>
	</div><!-- /header -->

	<div data-role="content">
		<div>
			<h3 class-="ui-li-heading">
				<?php
				echo $this->EntryH->getSubject($entry);
				?>
			</h3>
			<p class="ui-li-desc">
				<strong>
					<?php echo $entry['User']['username'] ?> – <?php echo $timeH->formatTime($entry['Entry']['time']); ?>
				</strong>
			</p>
		</div>
		<br />
		<div>
			<?php echo $bbcode->parse($entry['Entry']['text']); ?> 
		</div>
	</div><!-- /content -->
