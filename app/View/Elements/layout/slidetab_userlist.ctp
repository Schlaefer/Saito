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
	<ul class="slidetab_tree">
		<li>
			<?php echo  __('%s online (%s)',
							$this->Html->link(
										__('user_area_linkname'),
										'/users/index'
								),
							$HeaderCounter['user_registered']
					);
			?>
		</li>
		<li>
			<ul class="slidetab_subtree">
				<?php  foreach($UsersOnline as $user) : ?>
					<li>
						<?php // for performance reasons we don't use $this->Html->link() here ?>
						<a href="<?php echo $this->request->webroot; ?>users/view/<?php echo $user['User']['id']; ?>" class="<?php echo ($user['User']['id'] == $CurrentUser->getId()) ? 'slidetab-actUser' : ''  ?>">
							<?php echo $user['User']['username']; ?><?php
								if ($this->UserH->isAdmin($user['User'])) : ?><span class="super" title="<?php echo __('ud_admin'); ?>"><i class="fa fa-wrench"></i></span>
							<?php elseif ($this->UserH->isMod($user['User'])) : ?><span class="super" title="<?php echo __('ud_mod'); ?>"><i class="fa fa-gavel"></i></span>
							<?php  endif; ?>
							</a>
					</li>
				<?php  endforeach; ?>
			</ul>
		</li>
		<!-- @td @lo subthread -->
	</ul>
<?php $this->end('slidetab-content'); ?>