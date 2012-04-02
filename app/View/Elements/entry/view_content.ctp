<?php
	### setup ###
	if (!isset($last_action)) $last_action = null;
	###
?>

		<h2 class="postingheadline">
			<?php
				$subject = $this->EntryH->getSubject($entry);
				// only make subject a link if it is not in entries/view
//				debug($this->request->action); debug($last_action); debug($isAjax);
				if ($this->request->action !== 'preview' && ( $this->request->is('ajax') || $this->request->action === 'mix')) {
					echo $this->Html->link(
							$subject,
							array(
									'controller' => 'entries',
									'action' => 'view',
									// is not set/unknown when showing preview
									(isset($entry['Entry']['id'])) ? $entry['Entry']['id'] : null,
								),
							array(
									'class' => 'span_post_type',
									'escape'	=> false,
							)
						);
				} else {
					echo $subject;
				}
			?>
			<?php # echo $this->Html->link('('.$entry['Category']['category'].')', "@td", array ( 'class' => 'category_acs_'.$entry['Category']['accession'],)); ?>
			<?php 	echo "<span class='category_acs_{$entry['Category']['accession']}'>({$entry['Category']['category']})</span>";  ?>

		</h2>

		<div class="author">
			<?= __('forum_author_marking'); ?>

			<? if ($CurrentUser->isLoggedIn()) : ?>
				<?= $this->Html->link($entry['User']['username'],
																array( 'controller' => 'users', 'action' => 'view', $entry['User']['id'])
										); ?>,
				<?=  (!empty($entry['User']['user_place'])) ? $entry['User']['user_place'].',' : '' ;  ?>
			<? else : ?>
				<strong><?= $entry['User']['username'] ?></strong>,
			<? endif; ?>

			<?php /* <span title="<?php echo $this->TimeH->formatTime($entry['Entry']['time']); ?>"><?php echo $this->TimeH->formatTime($entry['Entry']['time'], 'glasen'); ?></span>, */ ?>
			<?php  echo $this->TimeH->formatTime($entry['Entry']['time']); ?>,
			<?= __('views_headline') ?>: <?= $entry['Entry']['views'] ?>
			<? if ( $entry['Entry']['nsfw'] ): ?>
				<div class="sprite-nbs-explicit"></div>
			<? endif; ?>
			<? if (isset($entry['Entry']['edited_by']) && !is_null($entry['Entry']['edited_by'])
							&& strtotime($entry['Entry']['edited']) > strtotime($entry['Entry']['time'])+( Configure::read('Saito.Settings.edit_delay') * 60 )
					): ?>
				<br/>
				<span class="entry_edited_info">
					(<?= __('board_edited_marking').' '.$entry['Entry']['edited_by'] . ", " . strftime("%d.%m.%Y, %H:%M",strtotime($entry['Entry']['edited'])) ?>)
				</span>

			<? endif; ?>
		</div>

		<div class='posting'> <?php echo $this->Bbcode->parse($entry['Entry']['text']); ?> </div>