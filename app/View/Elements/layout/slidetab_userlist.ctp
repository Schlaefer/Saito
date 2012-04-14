<?php Stopwatch::start('slidetab_userlist.ctp'); ?>
<?php  if ($CurrentUser->isLoggedIn() && $this->request->params['action'] == 'index' && $this->request->params['controller'] == 'entries') : ?>
	<?php echo $this->element('layout/slidetabs__header', array('id' => 'userlist', 'btn_class' => 'users_img')); ?>
				<ul class="slidetab_tree">
					<li>
						<?php echo  $this->Html->link(__('user_area_linkname'), '/users/index'); ?>  an Deck (<?php echo $HeaderCounter['user_registered']?>)
					</li>
					<li>
						<ul class="slidetab_subtree">
							<?php  foreach($UsersOnline as $user) : ?>
								<li>
									<?php // for performance reasons we don't use $this->Html->link() here ?>
									<a href="<?php echo $this->request->webroot; ?>users/view/<?php echo $user['User']['id']; ?>" class="<?php echo ($user['User']['id'] == $CurrentUser->getId()) ? 'act_user' : ''  ?>">
										<?php echo $user['User']['username']; ?></a><?php
										if ($this->UserH->isMod($user['User'])) : ?><span class="super" title="<?php echo __('ud_mod'); ?>">*</span>
										<?php  endif; ?>
								</li>
							<?php  endforeach; ?>
						</ul>
					</li>
					<!-- @td @lo subthread -->
				</ul>
	<?php echo $this->element('layout/slidetabs__footer'); ?>
<?php  endif; ?>
<?php	Stopwatch::stop('slidetab_userlist.ctp'); ?>
