<?php
if (isset($CurrentUser) && $CurrentUser->isLoggedIn() == FALSE ) {
	?>
	<a href="<?php echo $this->request->webroot; ?>users/register/"><?php echo __('register_linkname') ;?></a>
	<?php if ($this->request->params['action'] != 'login') { ?> 
		&nbsp;|&nbsp;
		<a href="<?php echo $this->request->webroot; ?>users/login/" id="showLoginForm" title="<?php echo __('login_btn') ;?>">
      <i class="icon-signin"></i>
      <?php echo __('login_btn') ;?>
    </a>
	<?php } ?>
	<?php
} else {
	if($CurrentUser['user_type'] == 'admin') {
		echo $this->Html->link(
        '<i class="icon-wrench"></i> ' . __('Forum Settings'),
        array('controller' => 'admins', 'action' => 'index', 'admin' => TRUE ),
        array( 'escape' => FALSE)). '&nbsp;|&nbsp;'  ;
	}
	?>
	<a href="<?php echo $this->request->webroot; ?>users/view/<?php echo $CurrentUser['id']; ?>" id="btn_view_current_user">
    <i class="icon-user"></i> <?php echo __('user_profile') ;?>
  </a>
	&nbsp;|&nbsp;
	<a href="<?php echo $this->request->webroot; ?>entries/index">
		<i class="icon-bookmark"></i> <?php echo __('Bookmarks'); ?>
	</a>
	&nbsp;|&nbsp;
	<?php
	echo $this->Html->link(
      '<i class="icon-signout"></i> ' . __('logout_linkname') ,
      '/users/logout',
      array( 'id' => 'btn_logout', 'escape' => FALSE ) );
}
?>
	&nbsp;|&nbsp;
  <?php echo $this->Html->link(
        '<i class="icon-question-sign"></i> ' . __('Help'),
        '#',
        array(
            'id'  => 'shp-show',
            'class'   => 'no-color',
            'onclick' => "saitoHelpShow();",
            'tooltip' => __('Help'),
            'data-title'   => __('Help'),
            'data-content'  => __('No help for this page available.'),
            'escape'  => FALSE,
            )
      );
      ?>