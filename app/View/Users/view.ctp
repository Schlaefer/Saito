<?php
	$this->start('headerSubnavLeft');
	echo $this->Layout->navbarItem(
		'<i class="fa fa-arrow-left"></i> ' . __('back_to_forum_linkname'),
		'/',
		['escape' => false]);
	$this->end();

	$this->element('users/menu');

	$urlToHistory = [
			'controller' => 'searches',
			'action' => 'advanced',
			'?' => ['name' => $user['User']['username'], 'nstrict' => 1]
	];

	$table = [
			[
					__('username_marking'),
					h($user['User']['username']) . " <span class='infoText'>({$this->UserH->type($user['User']['user_type'])})</span>",
				# @td user_type for mod and admin
			]
	];

	if ($user['User']['user_lock']) {
		$table[] = [
				__('user.set.lock.t'),
				$this->UserH->banned($user['User']['user_lock']),
		];
	}

	if (!empty($user['User']['user_real_name'])) {
		$table[] = [
				__('user_real_name'),
				h($user['User']['user_real_name'])
		];
	}

	if (!empty($user['User']['user_email']) &&
			$user['User']['personal_messages'] == true
	) {
		$_contact = $this->UserH->contact($user['User']);
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
				__('user_hp'),
				$this->UserH->homepage($user['User']['user_hp'])
		];
	}

	if (!empty($user['User']['user_place'])) {
		$table[] = [
				__('user_place'),
				h($user['User']['user_place'])
		];
	}

	if (Configure::read('Saito.Settings.map_enabled') && !empty($user['User']['user_place_lat'])) {
		$table[] = [
				'',
				$this->Map->map($user)
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

	// helpful postings
	if (!empty($user['User']['solves_count'])) {
		$table[] = [
				$this->EntryH->solvedBadge(),
				$user['User']['solves_count']
		];
	}

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
	<div class="panel">
		<?= $this->Layout->panelHeading(__('user.b.profile')) ?>
		<div class="panel-content">
			<table class='table th-left elegant'>
				<?= $this->Html->tableCells($table); ?>
			</table>
		</div>

		<?php
			$isUsersEntry = $CurrentUser->getId() == $user['User']['id'];
			$isMod = $CurrentUser->isMod();
			if ($isUsersEntry || $isMod):
				?>
				<div class="panel-footer panel-form">
					<?php
						// default edit link
						if ($isUsersEntry) {
							echo $this->Html->link(
									__('edit_userdata'),
									['action' => 'edit', $user['User']['id']],
									['id' => 'btn_user_edit', 'class' => 'btn btn-submit panel-footer-form-btn']
							);
						}

						if ($isMod) {
							$_menuItems = [];

							// lock user
							if ($CurrentUser->isAdmin() || $modLocking) {
								$_menuItems[] = $this->Html->link(
										'<i class="fa fa-ban"></i> ' . (($user['User']['user_lock']) ? __('Unlock') : __('Lock')),
										array(
												'controller' => 'users',
												'action' => 'lock',
												$user['User']['id']
										),
										array('escape' => false)
								);
							}

							if ($CurrentUser->isAdmin()) {
								// edit user
								$_menuItems[] = $this->Html->link(
										'<i class="fa fa-pencil"></i> ' . __('Edit'),
										array('action' => 'edit', $user['User']['id']),
										array('escape' => false)
								);
								$_menuItems[] = 'divider';

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

							if (!empty($_menuItems)) {
								echo $this->Layout->dropdownMenuButton($_menuItems,
										[
												'class' => 'btnLink btn-icon panel-footer-form-btn',
										]);
							}
						}
					?>
				</div>
			<?php endif; ?>
	</div>
	<div class="panel">
		<?= $this->Layout->panelHeading(__('user_recentposts')) ?>
		<div class="panel-content">
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
			<?php endif; ?>
		</div>
		<?php if ($hasMoreEntriesThanShownOnPage) : ?>
			<div class="panel-footer panel-form">
				<?= $this->Html->link(__('Show all'),
						$urlToHistory,
						['class' => 'panel-footer-form-bnt']) ?>
			</div>
		<?php endif; ?>
	</div>
</div>