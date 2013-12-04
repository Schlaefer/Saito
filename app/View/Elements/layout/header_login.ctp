<?php
	if (!isset($divider))	{
		$divider = '';
	}
	if (isset($CurrentUser) && $CurrentUser->isLoggedIn() == false) {
		?>
		<a href="<?= $this->request->webroot; ?>users/register/"
			 class='top-menu-item' rel="nofollow">
			<?= __('register_linkname') ?>
		</a>
		<?php if ($this->request->params['action'] != 'login') { ?>
			<?= $divider ?>
			<a href="<?php echo $this->request->webroot; ?>users/login/"
				 id="showLoginForm" title="<?= __('login_btn') ?>"
				 class='top-menu-item' rel="nofollow">
				<i class="fa fa-sign-in"></i>
				<span class="top-menu-item-text">
					<?= __('login_btn') ?>
				</span>
			</a>
		<?php
		}
	} else {
		if ($CurrentUser['user_type'] == 'admin') {
			echo $this->Html->link(
						'<i class="fa fa-wrench"></i> ' .
						'<span class="top-menu-item-text">' .
						__('Forum Settings') .
						'</span>',
						[
							'controller' => 'admins',
							'action' => 'index',
							'admin' => true
						],
						['class' => 'top-menu-item', 'escape' => false]
					) . $divider;
		}
		?>
		<a href="<?= $this->request->webroot; ?>users/view/<?= $CurrentUser['id'] ?>"
			 id="btn_view_current_user" class="top-menu-item">
			<i class="fa fa-user"></i>
			<span class="top-menu-item-text">
				<?= __('user_profile') ?>
			</span>
		</a>
		<?= $divider ?>
		<a href="<?= $this->request->webroot; ?>bookmarks/" class="top-menu-item">
			<i class="fa fa-bookmark"></i>
			<span class="top-menu-item-text">
				<?= __('Bookmarks') ?>
			</span>
		</a>
		<?= $divider ?>
		<?php
		echo $this->Html->link(
			'<i class="fa fa-sign-out"></i> ' .
			'<span class="top-menu-item-text">' .
			__('logout_linkname') .
			'</span>',
			'/users/logout',
			['id' => 'btn_logout', 'class' => 'top-menu-item', 'escape' => false]
		);
	}
?>
<?= $divider ?>
<button id="shp-show" class="btnLink no-color top-menu-item"
				data-title="<?= __('Help') ?>"
				data-content="<?= __('No help for this page available.') ?>">
	<i class="fa fa-question-circle"></i>
	<span class="top-menu-item-text">
		<?= __('Help') ?>
	</span>
</button>
