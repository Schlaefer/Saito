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
		if ($CurrentUser->permission('saito.core.admin.backend')) {
			echo $this->Html->link(
						$this->Layout->textWithIcon(h(__('ial.aa')), 'wrench'),
						'/admin',
						['class' => 'top-menu-item', 'escape' => false]
					) . $divider;
		}
		?>
		<a href="<?= $this->request->webroot; ?>users/view/<?= $CurrentUser['id'] ?>"
			 id="btn_view_current_user" class="top-menu-item">
			<?= $this->Layout->textWithIcon(__('user.b.profile'), 'user') ?>
		</a>
		<?php
			//= show additional nav-buttons
			$items = $SaitoEventManager->dispatch(
				'Request.Saito.View.MainMenu.navItem',
				['View' => $this]
			);
			if ($items) {
				foreach ($items as $item) {
					echo $divider;
					$link = $this->request->webroot . $item['url'];
					echo "<a href=\"{$link}\" class=\"top-menu-item\">{$item['title']}";
					echo '</a>';
				}
			}
		?>
		<?= $divider ?>
		<?php
		echo $this->Html->link(
			$this->Layout->textWithIcon(h(__('logout_linkname')), 'sign-out'),
			'/users/logout',
			['id' => 'btn_logout', 'class' => 'top-menu-item', 'escape' => false]
		);
	}
