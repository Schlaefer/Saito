<?php
	$_showBlocked = Configure::read('Saito.Settings.block_user_ui');
?>
<div class="box-content">
	<div class="l-box-header box-header">
		<div>
			<div class='c_first_child'></div>
			<div><h1><?= __('reg_users_hl'); ?></h1></div>
			<div class='c_last_child'>

			</div>
		</div>
	</div>
	<div class="content">
		<div class="table-menu">
			<?php
				$_sortBy = $this->Paginator->sort('username', __('username_marking'));
				$_sortBy .= ', ' . $this->Paginator->sort('User.user_type', __('user_type'));
				$_sortBy .= ', ' . $this->Paginator->sort('UserOnline.user_id',
								__('userlist_online'),
								[
										'direction' => 'desc'
								]);
				$_sortBy .= ', ' . $this->Paginator->sort('registered',
								__('registered'),
								[
										'direction' => 'desc'
								]);
				if ($_showBlocked) {
					$_sortBy .= ', ' . $this->Paginator->sort('user_lock',
									__('user_lock'),
									[
											'direction' => 'desc'
									]);
				}
				echo __('Sort by: %s', $_sortBy);
			?>
		</div>
		<table class="table th-left row-sep">
			<tbody>
			<?php
				foreach ($users as $user): ?>
					<tr>
						<td>
							<?=
								$this->Html->link(
										$user['User']['username'],
										[
												'controller' => 'users',
												'action' => 'view',
												$user['User']['id']
										]);
							?>
						</td>
						<td>
							<ul>
								<?php
									echo '<li>' . __($this->UserH->type($user['User']['user_type'])) . '</li>';
									if ($user['UserOnline']['logged_in']) {
										echo '<li>' . __('Online') . '</li>';
									}
									echo '<li>' . __('user_since %s',
													$this->TimeH->formatTime($user['User']['registered'],
															'custom',
															'%d.%m.%Y')) .
											'</li>';
									if ($_showBlocked && $user['User']['user_lock']):
										echo '<li>' . __('%s banned',
														$this->UserH->banned($user['User']['user_lock'])) . '</li>';
									endif;
								?>
							</ul>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
