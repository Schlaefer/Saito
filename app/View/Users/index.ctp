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
<table class="table border zebra">
	<?php
		echo $this->Html->tableHeaders(array(
				$this->Paginator->sort('username', __('username_marking')),
				$this->Paginator->sort('User.user_type', __('user_type')),
				__("userlist_email"),
				__("user_hp"),
				$this->Paginator->sort('UserOnline.user_id', __("userlist_online")),
//		__("user_lock"),
				$this->Paginator->sort('registered', __("registered")),
		));
	?>
	<?php foreach ( $users as $user) : ?>
	<?php echo
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