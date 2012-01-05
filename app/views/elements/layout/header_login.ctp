<?php
if ($CurrentUser->isLoggedIn() == FALSE ) {
	?>
	<a href="<?php echo $this->webroot; ?>users/register/"><?php echo __('register_linkname') ;?></a>
	<?php if ($this->params['action'] != 'login') { ?> 
		&nbsp;|&nbsp;
		<a href="<?php echo $this->webroot; ?>users/login/" id="showLoginForm" title="<?php echo __('login_btn') ;?>"><?php echo __('login_btn') ;?></a>
	<?php } ?>
	<?php
} else {
	if($CurrentUser['user_type'] == 'admin') {
		echo $html->link(__('Adminbereich @lo', true), array('controller' => 'admins', 'action' => 'index', 'admin' => TRUE )). '&nbsp;|&nbsp;'  ;
	}
	?>
	<a href="<?php echo $this->webroot; ?>users/view/<?php echo $CurrentUser->getId(); ?>" id="btn_view_current_user"><?php echo __('user_profile') ;?></a>
	&nbsp;|&nbsp;
	<?php
	echo $html->link(__('logout_linkname',true) , '/users/logout', array( 'id' => 'btn_logout' ) );
}
?>