<?php
	SDV($last_action, null);
	SDV($signature, false);
?>
	<div class="postingBody">
		<h2 class="postingBody-heading">
			<?php
				$subject = $this->EntryH->getSubject($entry);
				// only make subject a link if it is not in entries/view
				if ($this->request->action !== 'preview' &&
						($this->request->is('ajax') || $this->request->action === 'mix')
				) {
					echo $this->Html->link($subject,
							'/entries/view/' . $entry['Entry']['id'],
							['escape' => false]);
				} else {
					echo $subject;
				}
			?>
		</h2>
		<div class="postingBody-info">
      <span class="c-username">
        <?php  if ($CurrentUser->isLoggedIn()) : ?>
					<?php echo  $this->Html->link(
						$entry['User']['username'],
							'/users/view/' . $entry['User']['id']
					); ?>
				<?php
					else:
						echo $entry['User']['username'];
					endif;
				?>
      </span>
			–
			<?php if ($entry['Entry']['pid'] == 0) : ?>
				<span class='c-category acs-<?= $entry['Category']['accession']; ?>'
							title="<?php echo $entry['Category']['description']; ?> (<?= __d('nondynamic', 'category_acs_'.$entry['Category']['accession'].'_exp'); ?>)">
        <?= $entry['Category']['category']; ?>,
        </span>
			<?php endif; ?>

			<span class="meta">
				<?php  if ($CurrentUser->isLoggedIn()) : ?>
					<?php echo   (!empty($entry['User']['user_place'])) ? $entry['User']['user_place'].',' : '' ;  ?>
				<?php  endif; ?>

				<?php /* <span title="<?php echo $this->TimeH->formatTime($entry['Entry']['time']); ?>"><?php echo $this->TimeH->formatTime($entry['Entry']['time'], 'glasen'); ?></span>, */ ?>
				<?php
					echo $this->TimeH->formatTime($entry['Entry']['time']);
					if (isset($entry['Entry']['edited_by']) && !is_null($entry['Entry']['edited_by'])
							&& strtotime($entry['Entry']['edited']) > strtotime($entry['Entry']['time']) + ( Configure::read('Saito.Settings.edit_delay') * 60 )
					):
						echo ' – ';
						echo __('%s edited by %s',
							array(
								$this->TimeH->formatTime($entry['Entry']['edited']),
								$entry['Entry']['edited_by']
							)
						);
						echo ',';
					else :
						echo ',';
					endif;
					echo ' ' . __('views_headline') . ': ' . $entry['Entry']['views'];

					if (Configure::read('Saito.Settings.store_ip') && $CurrentUser->isMod()) {
						echo ', IP: ' . $entry['Entry']['ip'];
					}

					echo ' <span class="posting-badges">';
					echo $this->EntryH->getBadges($entry);
					echo '</span>';
				?>
			</span>
		</div>

		<div class='postingBody-text'>
			<?= $this->Bbcode->parse($entry['Entry']['text']) ?>
		</div>

		<?php if ($signature): ?>
			<div id="signature_<?= $entry['Entry']['id'] ?>" class="postingBody-signature">
				<div class="postingBody-signature-divider">
					<?= Configure::read('Saito.Settings.signature_separator') ?>
				</div>
				<?php
					$multimedia = ($CurrentUser->isLoggedIn()) ? !$CurrentUser['user_signatures_images_hide'] : true;
					echo $this->Bbcode->parse($entry['User']['signature'],
							array('multimedia' => $multimedia));
				?>
			</div>
		<?php endif; ?>
	</div>
