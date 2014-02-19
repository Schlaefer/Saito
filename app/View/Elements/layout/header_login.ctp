<?php
	if (!isset($divider))	{
		$divider = '';
	}
	if ($CurrentUser->isLoggedIn() == false) {
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
				<?= $this->Layout->textWithIcon(__('login_btn'), 'sign-in') ?>
			</a>
		<?php
		}
	} else {
		if ($CurrentUser['user_type'] == 'admin') {
			echo $this->Html->link(
						$this->Layout->textWithIcon(h(__('Forum Settings')), 'wrench'),
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
			<?= $this->Layout->textWithIcon(__('user_profile'), 'user') ?>
		</a>
		<?= $divider ?>
		<a href="<?= $this->request->webroot; ?>bookmarks/" class="top-menu-item">
			<?= $this->Layout->textWithIcon(__('Bookmarks'), 'bookmark') ?>
		</a>
		<?= $divider ?>
		<?php
		echo $this->Html->link(
			$this->Layout->textWithIcon(h(__('logout_linkname')), 'sign-out'),
			'/users/logout',
			['id' => 'btn_logout', 'class' => 'top-menu-item', 'escape' => false]
		);
	}
?>
