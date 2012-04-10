<?php
	### setup ###
	if (!isset($level)) $level = 0;
	if (!isset($last_action)) $last_action = null;
	###
?>

<div class="a_a">
	<div class="a_a_a">
	<?php if( $CurrentUser->isMod()) : ?>
		<div class="button_mod_panel <?php echo $entry['Entry']['id'];?>">
			<div class="left <?php echo $entry['Entry']['id'];?>">
				<div class="img_mod_panel"></div>
			</div>
			<div class="right">
				<ul>
					<?php if (isset($entry['rights']['isEditingForbidden']) && ($entry['rights']['isEditingForbidden'] == false)) : ?>
						<li>
							<?php echo $this->Html->link(
											__('edit_linkname'),
											array( 'controller' => 'entries', 'action' => 'edit', $entry['Entry']['id']),
											array ( 'class' => '' )
										);
							?>
						</li>
					<?php endif; ?>
					<?php if($entry['Entry']['pid'] == 0) : # @td these are thread functions and maybe go to another panel ?>
						<li>
							<?php
								echo $this->Ajax->link(
									($entry['Entry']['fixed'] == 0) ? __('fixed_set_entry_link') : __('fixed_unset_entry_link'),
									array(
										'controller'	=> 'entries',
										'action'			=> 'ajax_toggle',
										$entry['Entry']['id'],
										'fixed',
									),
									array(
										'class' 	=> 'fixed ' . $entry['Entry']['id'],
										'success'	=> "$('.fixed.{$entry['Entry']['id']}').html(data);",
										'inline'	=> true,
									)
								);
							?>
						</li>
						<li>
							<?php
								echo $this->Ajax->link(
										($entry['Entry']['locked'] == 0) ? __('locked_set_entry_link') : __('locked_unset_entry_link'),
										array(
												'controller' 	=> 'entries',
												'action'			=> 'ajax_toggle',
												$entry['Entry']['id'],
												'locked',
										),
										array(
												'class'		=> 'locked ' . $entry['Entry']['id'],
												'success'	=> "$('.locked.{$entry['Entry']['id']}').html(data)",
												'inline'	=> true,
										)
									);
							?>
						</li>
						<li>
							<br/>
							<?php
								echo $this->Html->link(
										__('delete_tree_link'),
										array(
												'controller'	=> 'entries',
												'action'			=> 'delete',
												$entry['Entry']['id'],
										),
										null,
										__('delete_tree_link_confirm_message')
								);
							?>
						</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	<?php endif; ?>

	<?php
		echo $this->element('/entry/view_content', array('entry' => $entry, 'level' => $level, )); # 'cache' => array('key' => $entry["Entry"]['id'], 'time' => '+1 day') ));
	?>
	<?php if($CurrentUser['user_signatures_hide'] == false) : ?>
		<div id="signature_<?php echo $entry['Entry']['id'];?>" class="signature">
		<div>
			<?= Configure::read('Saito.Settings.signature_separator') ?>
		</div>
			<?php
				$multimedia = ( $CurrentUser->isLoggedIn() ) ? !$CurrentUser['user_signatures_images_hide'] : true;
				echo $this->Bbcode->parse($entry['User']['signature'], array('multimedia' => $multimedia));
			?>
		</div>
	<?php endif; ?>

	</div> <!-- a_a_a -->
				<?php if ( $CurrentUser->isLoggedIn() ) : ?>
					<div id="a_a_b_<?php echo $entry['Entry']['id'];?>" class="c_a_a_b">
						<div>
						<?php
							// User is logged in AND we are not in the inline view of the tree in entries/view (there we answer directly)
							// @todo: this needs refactoring and commenting
							// debug($last_action); debug($this->request->action);
						if( $CurrentUser->isLoggedIn()
								&& (($last_action === $this->request->action && !$this->request->is('ajax')) || $last_action === 'index' || $this->request->action === 'mix')
								|| $last_action === 'edit' 
								|| $last_action === 'search' 
								// after posting a completely new thread the last action was the `add` form
								|| $last_action === 'add'
							):
						?>
							<div class="c_a_a_b_a c_first_child">
								<?php
										# @td MCV
										$answering_forbidden =  $entry['rights']['isAnsweringForbidden'];
										if ($answering_forbidden === 'locked') {
											echo $this->Html->image('locked.png', array("alt" => 'locked'));
										} elseif (!$answering_forbidden) {
											$result =  "scrollToBottom('#posting_formular_slider_bottom_".$entry['Entry']['id']."'); initViewAnswerForm();";

											 echo $this->Ajax->link(
																__('forum_answer_linkname'),
															 array(
																	 'controller'=>'entries',
																	 'action' => 'add',
																	 $entry['Entry']['id'],
																),
															 array(
																'onclick' => "entries_add_toggle({$entry['Entry']['id']});",
																'id' => 'forum_answer_' . $entry['Entry']['id'],
																'class' => 'btn_submit', 'accesskey' => "a" ,
																'update' => 'posting_formular_slider_' . $entry['Entry']['id'] ,
																'indicator' => 'spinner_'. $entry['Entry']['id'],
																'complete'	=> $result ,
																'inline'	=> true,

															)
											 );
										};
									?>
									<?php  if (isset($entry['rights']['isEditingAsUserForbidden']) && $entry['rights']['isEditingAsUserForbidden'] == false) : ?>
										&nbsp;
										<span class="small">
											<?php echo $this->Html->link(
														__('edit_linkname'),
														array( 'controller' => 'entries', 'action' => 'edit', $entry['Entry']['id']),
														array ( 'class' => 'button_edit', 'accesskey' => "e" )
														);
											?>
										</span>
									<?php endif; ?>

									<!--
									&nbsp;
									<? #@td löschen link ?>
									&nbsp;
									-->
									<span class="small">
										<!-- @td: img/lock.gif @td implement controller -->
										<?php echo $this->Html->link(
													'', #__('lock_linkname'),
													array( 'controller' => 'entries', 'action' => 'lock', $entry['Entry']['id']),
													array ( 'class' => 'editlink')
													);
										?>
									</span>
							</div> <!-- c_a_a_b_a -->
						<?php endif; ?>
							<div class="c_a_a_b_b"> 
							</div><!-- c_a_a_b_b -->
						<?php 
							$a_a_b_c = false;
							// flattr - Button
							if(	Configure::read('Saito.Settings.flattr_enabled') == TRUE 
									// flattr is activated by admin
									&& $entry['Entry']['flattr'] == TRUE 
									&& $entry['User']['flattr_uid'] == TRUE
								) :
								$a_a_b_c = $this->Flattr->button('', 
										array( 
											'uid' => $entry['User']['flattr_uid'],
											'language'	=> Configure::read('Saito.Settings.flattr_language'),
											'title' => $entry['Entry']['subject'] ,
											'description' => $entry['Entry']['subject'],
											'cat' => Configure::read('Saito.Settings.flattr_category'),
											'button' => 'compact',
										)
									);
							endif; 
						?>
						<?php if ($a_a_b_c) :?> 
							<div class="c_a_a_b_c c_last_child">
								<?php echo $a_a_b_c; ?>
							</div><!-- c_a_a_b_c -->
						<?php endif; ?>
						</div>
					</div><!-- a_a_b -->
				<?php endif; ?>
</div> <!-- a_a -->
<div class="a_b">
	<div id="posting_formular_slider_<?php echo $entry['Entry']['id']; ?>" class="posting_formular_slider" style="display:none;"  >
		<div id="spinner_<?php echo $this->request->data['Entry']['id']; ?>" class="spinner"></div>
	</div>
</div> <!-- a_b -->
<div id="posting_formular_slider_bottom_<?php echo $entry['Entry']['id']; ?>"></div>