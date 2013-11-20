<?php
	if (isset($CurrentUser) && $CurrentUser->isLoggedIn() == false) {
		?>
		<a href="<?= $this->request->webroot; ?>users/register/" rel="nofollow">
			<?= __('register_linkname') ?>
		</a>
		<?php if ($this->request->params['action'] != 'login') { ?>
			&nbsp;|&nbsp;
			<a href="<?php echo $this->request->webroot; ?>users/login/"
				 id="showLoginForm" title="<?= __('login_btn') ?>" rel="nofollow">
				<i class="fa fa-sign-in"></i>
				<?= __('login_btn') ?>
			</a>
		<?php
		}
	} else {
		if ($CurrentUser['user_type'] == 'admin') {
			echo $this->Html->link(
						'<i class="fa fa-wrench"></i> ' . __('Forum Settings'),
						[
							'controller' => 'admins',
							'action' => 'index',
							'admin' => true
						],
						['escape' => false]) . '&nbsp;|&nbsp;';
		}
		?>
		<a href="<?= $this->request->webroot; ?>users/view/<?= $CurrentUser['id'] ?>"
			 id="btn_view_current_user">
			<i class="fa fa-user"></i> <?= __('user_profile') ?>
		</a>
		&nbsp;|&nbsp;
		<a href="<?= $this->request->webroot; ?>bookmarks/">
			<i class="fa fa-bookmark"></i> <?= __('Bookmarks') ?>
		</a>
		&nbsp;|&nbsp;
		<?php
		echo $this->Html->link(
			'<i class="fa fa-sign-out"></i> ' . __('logout_linkname'),
			'/users/logout',
			['id' => 'btn_logout', 'escape' => false]
		);
	}
?>
&nbsp;|&nbsp;
<button id="shp-show" class="btnLink no-color"
				data-title="<?= __('Help') ?>"
				data-content="<?= __('No help for this page available.') ?>">
	<i class="fa fa-question-circle"></i>
	<?= __('Help') ?>
</button>
