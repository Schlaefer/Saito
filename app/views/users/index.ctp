<h1><?php echo __('reg_users_hl'); ?></h1>
<?php
	/*
	echo $this->Paginator->numbers();
	echo $this->Paginator->prev('« Previous ', null, null, array('class' => 'disabled'));
	echo $this->Paginator->next(' Next »', null, null, array('class' => 'disabled'));
	echo $this->Paginator->counter();
	 * 
	 */
?>
<table class="table_1">
	<?= $this->Html->tableHeaders( array (
		$this->Paginator->sort(__('username_marking'), 'username'),
		$this->Paginator->sort(__('user_type'), 'User.user_type'),
		__("userlist_email"),
		__("user_hp"),
		$this->Paginator->sort(__("userlist_online"), 'UserOnline.user_id'),
//		__("user_lock"),
		$this->Paginator->sort(__("registered"), 'registered'),

	));
	?>
	<?php foreach ( $users as $user) : ?>
	<?=
		$this->Html->tableCells(
				array (
					array (
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
						$this->UserH->minusIfEmpty($this->UserH->contact($user['User'])),
						$this->UserH->minusIfEmpty($this->UserH->homepage($user['User']['user_hp'])),
						($user['UserOnline']['logged_in']) ? 'Online' : $this->UserH->minusIfEmpty($a=''),
						$this->TimeH->formatTime($user['User']['registered']),
					),
				),
				array ( 'class' => 'a' ),
				array ( 'class' => 'b' )
		);
	?>
	<?php endforeach; ?>
</table>
		<?php

//		$this->Paginator->params['paging']['User']['options']['order'] =  array("User.user_type" => 'asc', "User.username" => 'asc') ;

//		debug($paginator);
		?>