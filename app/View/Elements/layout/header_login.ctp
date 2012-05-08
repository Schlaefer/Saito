<?php
if ($CurrentUser->isLoggedIn() == FALSE ) {
	?>
	<a href="<?php echo $this->request->webroot; ?>users/register/"><?php echo __('register_linkname') ;?></a>
	<?php if ($this->request->params['action'] != 'login') { ?> 
		&nbsp;|&nbsp;
		<a href="<?php echo $this->request->webroot; ?>users/login/" id="showLoginForm" title="<?php echo __('login_btn') ;?>"><?php echo __('login_btn') ;?></a>
	<?php } ?>
	<?php
} else {
	if($CurrentUser['user_type'] == 'admin') {
		echo $this->Html->link(__('Adminbereich @lo'), array('controller' => 'admins', 'action' => 'index', 'admin' => TRUE )). '&nbsp;|&nbsp;'  ;
	}
	?>
	<a href="<?php echo $this->request->webroot; ?>users/view/<?php echo $CurrentUser->getId(); ?>" id="btn_view_current_user"><?php echo __('user_profile') ;?></a>
	&nbsp;|&nbsp;
	<?php
	echo $this->Html->link(__('logout_linkname') , '/users/logout', array( 'id' => 'btn_logout' ) );
}
?>
	&nbsp;|&nbsp;
  <?php echo $this->Html->link(
        __('Help'),
        '#',
        array(
            'id'  => 'shp-show',
            'class'   => 'no-color',
            'onclick' => "saitoHelpShow();",
            'tooltip' => __('Help'),
            'data-title'   => __('Help'),
            'data-content'  => __('No help for this page available.'),
            )
      );
      ?>