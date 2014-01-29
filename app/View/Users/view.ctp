<?php
	$this->start('headerSubnavLeft');
	echo $this->Html->link(
			'<i class="fa fa-arrow-left"></i> ' . __('back_to_forum_linkname'),
			'/',
			array('class' => 'textlink', 'escape' => false));
	$this->end();
?>
<div class="user view">
<?php
	$urlToHistory = $this->Html->url(
			[
					'controller' => 'entries',
					'action' => 'search',
					'name' => $user['User']['username'],
					'month' => strftime('%m', strtotime($user['User']['registered'])),
					'year' => strftime('%Y', strtotime($user['User']['registered'])),
					'adv' => 1
			],
			true
	);

	$table = [
			[
					__('username_marking'),
					$user['User']['username'] . " <span class='infoText'>({$this->UserH->type($user['User']['user_type'])})</span>",
				# @td user_type for mod and admin
			]
	];

	if ($user['User']['user_lock']) {
		$table[] = [
				__('user_block'),
				$this->UserH->banned($user['User']['user_lock']),
		];
	}

	if (!empty($user['User']['user_real_name'])) {
		$table[] = [
				__('user_real_name'),
				$this->UserH->minusIfEmpty($user['User']['user_real_name'])
		];
	}

	if (!empty($user['User']['user_email']) &&
			$user['User']['personal_messages'] == true
	) {
		$_contact = $this->UserH->minusIfEmpty($this->UserH->contact($user['User']));
		if ($CurrentUser->isAdmin()) {
			$_contact .= ' ' . $this->Layout->infoText(
							'(' .
							$this->Text->autoLinkEmails($user['User']['user_email']) .
							')'
					);
		}
		$table[] = [__('Contact'), $_contact];
	}

	if (!empty($user['User']['user_hp'])) {
		$table[] = [
				__("user_hp"),
				$this->UserH->minusIfEmpty($this->UserH->homepage($user['User']['user_hp']))
		];
	}

	if (!empty($user['User']['user_place'])) {
		$table[] = [
				__('user_place'),
				$user['User']['user_place']
		];
	}

	$table[] = [
			__('user_since'),
			strftime(__('date_short'),
					strtotime($user['User']['registered']))
	];

	// number of postings
	if (Configure::read('Saito.Settings.userranks_show')) {
		$_userRank = $this->Layout->infoText(' (' .
				$this->UserH->userRank($user['User']['number_of_entries']) .
				')');;
	} else {
		$_userRank = '';
	}
	$table[] = [
			__('user_postings'),
			$this->Html->link($user['User']['number_of_entries'],
					$urlToHistory,
					['escape' => false]) . $_userRank
	];

	// profile
	if (!empty($user['User']['profile'])) {
		$table[] = [
				__('user_profile'),
				$this->Bbcode->parse($user['User']['profile'])
		];
	}

	if (!empty($user['User']['signature'])) {
		$table[] = [
				__('user_signature'),
				$this->Bbcode->parse($user['User']['signature'])
		];
	}

	// flattr Button
	if (Configure::read('Saito.Settings.flattr_enabled') == true &&
			!empty($entry['User']['flattr_uid']) &&
			$user['User']['flattr_allow_user'] == true
	) {
		$table[] = [
				__('flattr'),
				$this->Flattr->button('',
						array(
								'uid' => $user['User']['flattr_uid'],
								'language' => Configure::read('Saito.Settings.flattr_language'),
								'title' => '[' . $_SERVER['HTTP_HOST'] . '] ' . $user['User']['username'],
								'description' => '[' . $_SERVER['HTTP_HOST'] . '] ' . $user['User']['username'],
								'cat' => Configure::read('Saito.Settings.flattr_category'),
								'button' => 'compact',
						)
				)
		];
	}
?>
<div class="users view">
	<div class="box-content">
		<div class="l-box-header box-header">
			<div>
				<div class='c_first_child'></div>
				<div>
					<h1>
						<?= __('Profile') ?>
					</h1>
				</div>
				<div class='c_last_child'></div>
			</div>
		</div>
		<div class="content">
			<table class='table th-left elegant'>
				<?= $this->Html->tableCells($table); ?>
			</table>
		</div>

		<?php
			$isUsersEntry = $CurrentUser->getId() == $user['User']['id'];
			$isMod = $CurrentUser->isMod();
			if ($isUsersEntry || $isMod):
				?>
				<div class="l-box-footer box-footer-form">
					<?php
						// default edit link
						if ($isUsersEntry) {
							echo $this->Html->link(
									__('edit_userdata'),
									array('action' => 'edit', $user['User']['id']),
									array('id' => 'btn_user_edit', 'class' => 'btn btn-submit')
							);
						}

						if ($isMod) {
							$_menuItems = [];

							// lock user
							if ($CurrentUser->isAdmin() ||
									($CurrentUser->isMod() &&
											Configure::read('Saito.Settings.block_user_ui'))
							) {
								$_menuItems[] = $this->Html->link(
										'<i class="fa fa-ban"></i> ' . (($user['User']['user_lock']) ? __('Unlock') : __('Lock')),
										array(
												'controller' => 'users',
												'action' => 'lock',
												$user['User']['id']
										),
										array('escape' => false)
								);

								if ($CurrentUser->isAdmin()) {
									// edit user
									$_menuItems[] = $this->Html->link(
											'<i class="fa fa-pencil"></i> ' . __('Edit'),
											array('action' => 'edit', $user['User']['id']),
											array('escape' => false)
									);
									$_menuItems[] = 'divider';

								}
								// delete user
								$_menuItems[] = $this->Html->link(
										'<i class="fa fa-trash-o"></i> ' . __('Delete'),
										array(
												'controller' => 'users',
												'action' => 'delete',
												$user['User']['id'],
												'admin' => true
										),
										array('escape' => false)
								);
							}

							echo $this->Layout->dropdownMenuButton($_menuItems,
									[
											'class' => 'btnLink js-button shp shp-right',
											'data-title' => __('Help'),
											'data-content' => __('button_mod_panel_shp')
									]);
						}
					?>
				</div> <!-- #box-footer.form -->
			<?php endif; ?>
	</div>
	<div class="box-content">
		<div class="l-box-header box-header">
			<div>
				<div class='c_first_child'></div>
				<div>
					<h1>
						<?= __('user_recentposts'); ?>
					</h1>
				</div>
				<div class='c_last_child'></div>
			</div>
		</div>
		<div class="content">
			<?php if (empty($lastEntries)): ?>
				<?=
				$this->element(
						'generic/no-content-yet',
						['message' => __('No entries created yet.')]
				); ?>
			<?php else: ?>
				<ul class="threadCollection-node root">
					<?php foreach ($lastEntries as $entry): ?>
						<li>
							<?= $this->EntryH->threadCached($entry, $CurrentUser); ?>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php if ($hasMoreEntriesThanShownOnPage) : ?>
					<p style="margin: 0.5em 1em">
						<?= $this->Html->link(__('Show all'), $urlToHistory) ?>
					</p>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</div>