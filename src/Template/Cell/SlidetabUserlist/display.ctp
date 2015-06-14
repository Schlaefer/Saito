<?php $this->start('slidetab-tab-button'); ?>
	<div class="btn-slidetabUserlist">
		<div id="slidetabUserlist-counter" class='slidetab-tab-info'
				 style="display: <?php echo $isOpen ? 'none' : 'block' ?>;">
			<div class="slidetabUserlist-counter-inner">
				<?= $registered ?>
			</div>
		</div>
		<i class="fa fa-users fa-lg"></i>
	</div>
<?php $this->end('slidetab-tab-button'); ?>
<?php $this->start('slidetab-content'); ?>
	<div class="slidetab-header">
		<h4>
			<?= __(
				'{0} online ({1})',
				[
					$this->Html->link(__('user_area_linkname'), '/users/index'),
					$registered
				]
			);
			?>
		</h4>
	</div>
	<div class="slidetab-content">
		<ul class="slidetab-list">
			<?php foreach ($online as $userOnline) :
				$user = $userOnline->user;
				?>
				<li>
					<?php // for performance reasons we don't use $this->Html->link() here ?>
					<a href="<?= $this->request->webroot; ?>users/view/<?= $user->get('id') ?>"
						 class="<?= ($user->get('id') == $CurrentUser->getId()) ? 'slidetab-actUser' : '' ?>">
						<?php
                            $role = $user->getRole();
							if ($role === 'admin') {
								$_title = __('user.type.admin');
								$_icon = 'fa-admin';
							} elseif ($role === 'mod') {
								$_title = __('user.type.mod');
								$_icon = 'fa-mod';
							} else {
								$_title = __('user.type.user');
								$_icon = 'fa-user';
							}
						?>
						<span class="slidetab-userlist-icon" title="<?= $_title ?>">
								<i class="fa <?= $_icon ?>"></i>
						</span>
						<?= h($user->get('username')) ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php $this->end('slidetab-content'); ?>
<?= $this->element('Cell/slidetabs'); ?>
