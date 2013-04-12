<div class="box-content">
	<div class="l-box-header box-header">
		<div>
			<div class='c_first_child'></div>
			<div><h1><?= __('reg_users_hl'); ?></h1></div>
			<div class='c_last_child'></div>
		</div>
	</div>
	<div class="content">
		<table class="table table-clean table-zebra">
			<thead>
				<?php
				$tableHeaders = array(
					$this->Paginator->sort('username', __('username_marking')),
					$this->Paginator->sort('User.user_type', __('user_type')),
					$this->Paginator->sort('UserOnline.user_id', __("userlist_online")),
					$this->Paginator->sort('registered', __("registered")),
				);
				if( Configure::read('Saito.Settings.block_user_ui') ) :
					$tableHeaders[] = $this->Paginator->sort('user_lock', __('user_lock'));
				endif;
				echo $this->Html->tableHeaders($tableHeaders);
				?>
			</thead>
			<tbody>
				<?php foreach ( $users as $user) : ?>
					<?php
					$tableCells = array (
						'<strong>'
								. $this->Html->link(
							$user['User']['username'],
							array(
								'controller' => 'users',
								'action' => 'view',
								$user['User']['id'])
						)
								. '</strong>',
						$this->UserH->type($user['User']['user_type']), # @td translate
						($user['UserOnline']['logged_in']) ? 'Online' : $this->UserH->minusIfEmpty($a=''),
						$this->TimeH->formatTime($user['User']['registered'])
					);
					if( Configure::read('Saito.Settings.block_user_ui') ) :
						$tableCells[] = $this->UserH->banned($user['User']['user_lock']);
					endif;
					echo $this->Html->tableCells(
						array ( $tableCells ),
						array ( 'class' => 'a' ),
						array ( 'class' => 'b' )
					);
					?>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
