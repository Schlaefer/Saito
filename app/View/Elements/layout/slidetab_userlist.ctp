<?php $this->start('slidetab-header'); ?>
	<div class="btn-slidetabUserlist">
		<div id="slidetabUserlist-counter" class='slidetab-tab-info'
				 style="display: <?php echo $isOpen ? 'none' : 'block' ?>;">
			<div class="slidetabUserlist-counter-inner">
				<?php echo $HeaderCounter['user_registered']; ?>
			</div>
		</div>
		<i class="fa fa-users fa-lg"></i>
	</div>
<?php $this->end('slidetab-header'); ?>
<?php $this->start('slidetab-content'); ?>
	<h4>
		<?php echo __('%s online (%s)',
			$this->Html->link(
				__('user_area_linkname'),
				'/users/index'
			),
			$HeaderCounter['user_registered']
		);
		?>
	</h4>
	<ul class="slidetab-list">
		<?php foreach ($UsersOnline as $user) : ?>
			<li>
				<?php // for performance reasons we don't use $this->Html->link() here ?>
				<a href="<?php echo $this->request->webroot; ?>users/view/<?php echo $user['User']['id']; ?>"
					 class="<?php echo ($user['User']['id'] == $CurrentUser->getId()) ? 'slidetab-actUser' : '' ?>">
					<?php
						if ($this->UserH->isAdmin($user['User'])) {
							$_title = __('ud_admin');
							$_icon = 'fa-admin';
						} elseif ($this->UserH->isMod($user['User'])) {
							$_title = __('ud_mod');
							$_icon = 'fa-mod';
						} else {
							$_title = __('ud_user');
							$_icon = 'fa-user';
						}
					?>
					<span class="slidetab-userlist-icon" title="<?= $_title ?>">
							<i class="fa <?= $_icon ?>"></i>
					</span>
					<?= $user['User']['username']; ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
<?php $this->end('slidetab-content'); ?>