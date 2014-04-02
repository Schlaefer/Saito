<?php
	SDV($last_action, null);
	SDV($signature, false);
?>
	<article class="postingBody">
		<header>
			<h2 class="postingBody-heading">
				<?php
					$subject = $this->EntryH->getSubject($entry);
					// only make subject a link if it is not in entries/view
					if ($this->request->action !== 'preview' &&
							($this->request->is('ajax') || $this->request->action === 'mix')
					) {
						echo $this->Html->link($subject,
								'/entries/view/' . $entry['Entry']['id'],
								['escape' => false, 'class' => 'et']);
					} else {
						echo $subject;
					}
				?>
			</h2>
		</header>
		<aside class="postingBody-info">
				<span class='c-category acs-<?= $entry['Category']['accession']; ?>'
							title="<?php echo $entry['Category']['description']; ?> (<?= __d('nondynamic', 'category_acs_'.$entry['Category']['accession'].'_exp'); ?>)">
				<?= $entry['Category']['category']; ?>
				</span>
			–
				<span class="c-username">
					<?= $this->Layout->linkToUserProfile($entry['User'], $CurrentUser); ?>,
				</span>

				<span class="meta">
					<?php
						if (!empty($entry['User']['user_place'])) {
							echo h($entry['User']['user_place']) . ', ';
						}

						echo $this->TimeH->formatTime($entry['Entry']['time']);

						if (!empty($entry['Entry']['edited_by'])) {
							$editDelay = strtotime($entry['Entry']['time']) +
									Configure::read('Saito.Settings.edit_delay');
							if (strtotime($entry['Entry']['edited']) > $editDelay) {
								echo ' – ';
								echo __('%s edited by %s',
										array(
												$this->TimeH->formatTime($entry['Entry']['edited']),
												$entry['Entry']['edited_by']
										)
								);
							}
						}

						// SEO: removes keyword "views"
						if ($CurrentUser->isLoggedIn()) {
							echo ', ' . __('views_headline') . ': ' . $entry['Entry']['views'];
						}

						if (Configure::read('Saito.Settings.store_ip') && $CurrentUser->isMod()) {
							echo ', IP: ' . $entry['Entry']['ip'];
						}

						echo ' <span class="posting-badges">';
						echo $this->EntryH->getBadges($entry);
						echo '</span>';
					?>
				</span>
		</aside>

		<div class='postingBody-text'>
			<?= $this->Bbcode->parse($entry['Entry']['text']) ?>
		</div>

		<?php if ($signature): ?>
			<footer id="signature_<?= $entry['Entry']['id'] ?>" class="postingBody-signature">
				<div class="postingBody-signature-divider">
					<?= Configure::read('Saito.Settings.signature_separator') ?>
				</div>
				<?php
					$multimedia = ($CurrentUser->isLoggedIn()) ? !$CurrentUser['user_signatures_images_hide'] : true;
					echo $this->Bbcode->parse($entry['User']['signature'],
							array('multimedia' => $multimedia));
				?>
			</footer>
		<?php endif; ?>
	</article>