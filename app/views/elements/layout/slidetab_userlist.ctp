<?php Stopwatch::start('slidetab_userlist.ctp'); ?>
<? if ($CurrentUser->isLoggedIn() && $this->params['action'] == 'index' && $this->params['controller'] == 'entries') : ?>
	<?php echo $this->element('layout/slidetabs__header', array('id' => 'userlist', 'btn_class' => 'users_img')); ?>
				<ul class="slidetab_tree">
					<li>
						<?= $html->link(__('user_area_linkname', true), '/users/index'); ?>  an Deck (<?=$HeaderCounter['user_registered']?>)
					</li>
					<li>
						<ul class="slidetab_subtree">
							<? foreach($UsersOnline as $user) : ?>
								<li>
									<?php // for performance reasons we don't use $html->link() here ?>
									<a href="<?php echo $this->webroot; ?>users/view/<?php echo $user['User']['id']; ?>" class="<?php echo ($user['User']['id'] == $CurrentUser->getId()) ? 'act_user' : ''  ?>">
										<?php echo $user['User']['username']; ?></a><?php
										if ($userH->isMod($user['User'])) : ?><span class="super" title="<?php echo __('ud_mod'); ?>">*</span>
										<? endif; ?>
								</li>
							<? endforeach; ?>
						</ul>
					</li>
					<!-- @td @lo subthread -->
				</ul>
	<?php echo $this->element('layout/slidetabs__footer'); ?>
<? endif; ?>
<?php	Stopwatch::stop('slidetab_userlist.ctp'); ?>
