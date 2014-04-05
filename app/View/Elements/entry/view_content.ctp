<?php
	SDV($last_action, null);
	SDV($signature, false);
	$schemaMeta = [];
?>
	<article itemscope itemtype="http://schema.org/Article" class="postingBody">
		<header>
			<h2 itemprop="headline name" class="postingBody-heading">
				<?php
					$subject = $this->EntryH->getSubject($entry);
					$url = $this->Html->url('/entries/view/' . $entry['Entry']['id'], true);
					$schemaMeta['url'] = $url;
					// only make subject a link if it is not in entries/view
					if ($this->request->action !== 'preview' &&
							($this->request->is('ajax') || $this->request->action === 'mix')
					) {
						$subject = $this->Html->link($subject,
								$url,
								['escape' => false, 'class' => 'et']);
					}
					echo $subject;
				?>
			</h2>
		</header>
		<aside class="postingBody-info">
				<span class='c-category acs-<?= $entry['Category']['accession']; ?>'
							title="<?php echo $entry['Category']['description']; ?> (<?= __d('nondynamic', 'category_acs_'.$entry['Category']['accession'].'_exp'); ?>)">
				<?= $entry['Category']['category']; ?>
				</span>
			–
				<span itemscope itemprop="author" itemtype="http://schema.org/Person">
					<span itemprop="name" class="c-username">
						<?=
							$this->Layout->linkToUserProfile($entry['User'], $CurrentUser);
						?></span>,
				</span>

				<span class="meta">
					<?php
						if (!empty($entry['User']['user_place'])) {
							echo h($entry['User']['user_place']) . ', ';
						}

						echo $this->TimeH->formatTime($entry['Entry']['time']);
						$schemaMeta['datePublished'] = date('c',
								strtotime($entry['Entry']['time']));

						if (!empty($entry['Entry']['edited_by'])) {
							$editDelay = strtotime($entry['Entry']['time']) +
									Configure::read('Saito.Settings.edit_delay');
							if (strtotime($entry['Entry']['edited']) > $editDelay) {
								echo ' – ';
								echo __('%s edited by %s',
										[
												$this->TimeH->formatTime($entry['Entry']['edited']),
												$entry['Entry']['edited_by']
										]
								);
							}
							$schemaMeta['dateModified'] = date('c', strtotime($entry['Entry']['edited']));
						}

						// SEO: removes keyword "views"
						if ($CurrentUser->isLoggedIn()) {
							echo ', ' . __('views_headline') . ': ' . $entry['Entry']['views'];
						}
						$schemaMeta['interactionCount'] = "UserPageVisits:{$entry['Entry']['views']}";

						if (Configure::read('Saito.Settings.store_ip') && $CurrentUser->isMod()) {
							echo ', IP: ' . $entry['Entry']['ip'];
						}

						echo ' <span class="posting-badges">';
						echo $this->EntryH->getBadges($entry);
						echo '</span>';
					?>
				</span>
		</aside>

		<div itemprop="articleBody text" class='postingBody-text'>
			<?= $this->Bbcode->parse($entry['Entry']['text']) ?>
		</div>

		<?php if ($signature): ?>
			<footer class="postingBody-signature">
				<div class="postingBody-signature-divider">
					<?= Configure::read('Saito.Settings.signature_separator') ?>
				</div>
				<?php
					$multimedia = ($CurrentUser->isLoggedIn()) ? !$CurrentUser['user_signatures_images_hide'] : true;
					echo $this->Bbcode->parse($entry['User']['signature'],
							array('multimedia' => $multimedia));
				?>
			</footer>
			<?php
				endif;
				array_walk($schemaMeta, function ($value, $attribute) {
							switch ($attribute) {
								case 'url':
									echo "<link itemprop=\"$attribute\" href=\"$value\"/>";
									break;
								default:
									echo "<meta itemprop=\"$attribute\" content=\"$value\"/>";
							}
						});
			?>
	</article>