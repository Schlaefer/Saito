<h1><?php __('reg_users_hl'); ?></h1>
<?php
	/*
	echo $paginator->numbers();
	echo $paginator->prev('« Previous ', null, null, array('class' => 'disabled'));
	echo $paginator->next(' Next »', null, null, array('class' => 'disabled'));
	echo $paginator->counter();
	 * 
	 */
?>
<table class="table_1">
	<?= $html->tableHeaders( array (
		$paginator->sort(__('username_marking', true), 'username'),
		$paginator->sort(__('user_type', true), 'User.user_type'),
		__("userlist_email", true),
		__("user_hp", true),
		$paginator->sort(__("userlist_online", true), 'UserOnline.user_id'),
//		__("user_lock", true),
		$paginator->sort(__("registered", true), 'registered'),

	));
	?>
	<?php foreach ( $users as $user) : ?>
	<?=
		$html->tableCells(
				array (
					array (
						'<strong>'	
						. $html->link(
										$user['User']['username'],
										array(
												'controller' => 'users',
												'action' => 'view',
												$user['User']['id'])
									)
						. '</strong>',
						$userH->type($user['User']['user_type']), # @td translate
						$userH->minusIfEmpty($userH->contact($user['User'])),
						$userH->minusIfEmpty($userH->homepage($user['User']['user_hp'])),
						($user['UserOnline']['logged_in']) ? 'Online' : $userH->minusIfEmpty($a=''),
						$timeH->formatTime($user['User']['registered']),
					),
				),
				array ( 'class' => 'a' ),
				array ( 'class' => 'b' )
		);
	?>
	<?php endforeach; ?>
</table>
		<?php

//		$paginator->params['paging']['User']['options']['order'] =  array("User.user_type" => 'asc', "User.username" => 'asc') ;

//		debug($paginator);
		?>