<div class="box-content">
	<div class="l-box-header box-header">
		<div>
			<div class='c_first_child'></div>
			<div><h1><?= __('reg_users_hl'); ?></h1></div>
			<div class='c_last_child'></div>
		</div>
	</div>
	<div class="content">
		Sort by:
		<?php
		echo $this->Paginator->sort('username', __('username_marking'));
			echo ", ";
		echo $this->Paginator->sort('User.user_type', __('user_type'));
			echo ", ";
		echo $this->Paginator->sort('UserOnline.user_id', __('userlist_online'));
			echo ", ";
		echo $this->Paginator->sort('registered', __('registered'));
			?>
		<table class="table table-clean table-zebra">
			<!-- thead>
			<?php
				$_showBlocked = Configure::read('Saito.Settings.block_user_ui');
				$tableHeaders = [
					$this->Paginator->sort('username', __('username_marking')),
					$this->Paginator->sort('User.user_type', __('user_type')),
					// $this->Paginator->sort('UserOnline.user_id', __('userlist_online')),
					// $this->Paginator->sort('registered', __('registered')),
				];
				if($_showBlocked) {
					$tableHeaders[] = $this->Paginator->sort('user_lock', __('user_lock'));
				}
				echo $this->Html->tableHeaders($tableHeaders);
				?>
			</thead -->
			<tbody>
			<?php
				foreach ($users as $user):
					$tableCells = [
						$this->Html->link(
							$user['User']['username'],
							[
								'controller' => 'users',
								'action' => 'view',
								$user['User']['id']
							]
						),
						// @todo translate
						$this->UserH->type($user['User']['user_type']) .
						'<br>' . ($user['UserOnline']['logged_in'] ? 'Online' : 'Not Online') .
				'<br> Registriert seit: ' . $this->TimeH->formatTime($user['User']['registered'], 'custom', '%d.%m.%Y')
						/*
				'Type:' . $this->UserH->type($user['User']['user_type'])
				. '<br> Online:' . ($user['UserOnline']['logged_in']) ? 'Online' : $this->UserH->minusIfEmpty($a = '')
				'Registred:' . $this->TimeH->formatTime($user['User']['registered'])
				*/
					];
					if ($_showBlocked) {
						$tableCells[] = $this->UserH->banned($user['User']['user_lock']);
					};
					echo $this->Html->tableCells(
						[$tableCells],
						['class' => 'a'],
						['class' => 'b']
					);
				endforeach;
			?>
			</tbody>
		</table>
	</div>
</div>
