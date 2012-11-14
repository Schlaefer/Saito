<?php
	### setup ###
	if (!isset($level)) $level = 0;
	if (!isset($last_action)) $last_action = null;
  $editLinkIsShown = FALSE;
	###
?>
<div class="js-entry-view-core" data-id="<?php echo $entry['Entry']['id'] ?>">
	<div class="a_a">
		<div class="a_a_a">

		<?php
			echo $this->element('/entry/view_content', array('entry' => $entry, 'level' => $level, )); # 'cache' => array('key' => $entry["Entry"]['id'], 'time' => '+1 day') ));
		?>
		<?php if(!$CurrentUser['user_signatures_hide'] && !empty($entry['User']['signature'])) : ?>
			<div id="signature_<?php echo $entry['Entry']['id'];?>" class="signature">
			<div>
				<?php echo Configure::read('Saito.Settings.signature_separator') ?>
			</div>
				<?php
					$multimedia = ( $CurrentUser->isLoggedIn() ) ? !$CurrentUser['user_signatures_images_hide'] : true;
					echo $this->Bbcode->parse($entry['User']['signature'], array('multimedia' => $multimedia));
				?>
			</div>
		<?php endif; ?>

		</div> <!-- a_a_a -->
	</div> <!-- a_a -->
	<?php if (!empty($showAnsweringPanel)): ?>
		<div id="box-footer-entry-actions-<?php echo $entry['Entry']['id'];?>" class="l-box-footer box-footer-form">
			<div style="float:right">
						<?php
						// flattr - Button
						if (Configure::read('Saito.Settings.flattr_enabled') == TRUE
								// flattr is activated by admin
								&& $entry['Entry']['flattr'] == TRUE
								&& $entry['User']['flattr_uid'] == TRUE
						) :
							echo $this->Flattr->button('',
									array(
									'uid'								 => $entry['User']['flattr_uid'],
									'language'					 => Configure::read('Saito.Settings.flattr_language'),
									'title'							 => $entry['Entry']['subject'],
									'description'				 => $entry['Entry']['subject'],
									'cat'								 => Configure::read('Saito.Settings.flattr_category'),
									'button'						 => 'compact',
									)
							);
						endif;
						?>
					</div>

									<?php
											# @td MCV
											$answering_forbidden =  $entry['rights']['isAnsweringForbidden'];
											if ($answering_forbidden === 'locked') { ?>
												<i
													class="icon-lock icon-huge shp shp-right"
													data-title="<?php echo __('Help'); ?>"
													data-content="<?php echo __('answering_forbidden_locked_shp'); ?>"
													></i>
											<?php
											} elseif (!$answering_forbidden) {
												$result =  "
													if(!_isScrolledIntoView($('#posting_formular_slider_bottom_".$entry['Entry']['id']."'))) {
														scrollToBottom('#posting_formular_slider_bottom_".$entry['Entry']['id']."');
													}
													initViewAnswerForm();";

												echo $this->Js->link(
																	__('forum_answer_linkname'),
																array(
																		'controller'=>'entries',
																		'action' => 'add',
																		$entry['Entry']['id'],
																	),
																array(
																	'id' => 'forum_answer_' . $entry['Entry']['id'],
																	'class' => 'btn btn-submit', 'accesskey' => "a" ,
																	'update' => '#posting_formular_slider_' . $entry['Entry']['id'] ,
																	'complete'	=> $result,
																	'buffer'	=> false,
																	'beforeSend' => "postings.get({$entry['Entry']['id']}).set({isAnsweringFormShown: true});",
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
								array ( 'class' => 'btn btn-edit', 'accesskey' => "e" )
								);
					?>
				</span>
			<?php endif; ?>

			<?php if( $CurrentUser->isMod()) : ?>
				<?php $isModMenuPopulated = false; ?>
				<?php $this->start('modMenu'); ?>
				&nbsp;
			<div class="l-button_mod_panel-wrapper">
				<div class="button_mod_panel <?php echo $entry['Entry']['id'];?>" >
					<div class="btn-group">
						<button class="btn dropdown-toggle btn-mini" data-toggle="dropdown">
							<i class="icon-wrench"></i>
							&nbsp;
							<i class="icon-caret-down"></i>
							</button>
						<ul class="dropdown-menu">
						<?php
							if (isset($entry['rights']['isEditingForbidden']) && ($entry['rights']['isEditingForbidden'] == false)) :
								$isModMenuPopulated = true;
								$editLinkIsShown = TRUE;
							?>
							<li>
								<?php echo $this->Html->link(
												'<i class="icon-pencil"></i> ' . __('edit_linkname'),
												array( 'controller' => 'entries', 'action' => 'edit', $entry['Entry']['id']),
												array ( 'escape' => FALSE )
											);
								?>
							</li>
						<?php endif; ?>
						<?php if($entry['Entry']['pid'] == 0): ?>
							<?php $isModMenuPopulated = true; ?>
							<?php if ($editLinkIsShown): ?>
								<li class="divider"></li>
							<?php endif; ?>
							<?php
								$ajax_toggle_options = array(
										'fixed' => 'icon-pushpin',
										'locked' => 'icon-lock'
								);
								foreach($ajax_toggle_options as $key => $icon):
										echo '<li>';
										echo $this->Js->link(
											'<i class="'.$icon.'"></i>&nbsp;'
											. '<span id="title-entry_' . $key . '-' . $entry['Entry']['id'] . '">'
											. (($entry['Entry'][$key] == 0) ? __d('nondynamic', $key . '_set_entry_link') : __d('nondynamic', $key . '_unset_entry_link'))
											. '</span>',
											array(
												'controller'	=> 'entries',
												'action'			=> 'ajax_toggle',
												$entry['Entry']['id'],
												$key,
											),
											array(
												'id'			 => 'btn-entry_' . $key . '-' . $entry['Entry']['id'],
												'success'	=> "$('#title-entry_{$key}-{$entry['Entry']['id']}').html(data);",
												'buffer'	=> false,
												'escape'  => FALSE,
											)
										);
										echo '</li>';
								endforeach;
								unset($ajax_toggle_options);
							?>
							<li class="divider"></li>
							<li>
								<?php
									echo $this->Html->link(
											'<i class="icon-resize-small"></i>&nbsp;' . __('merge_tree_link'),
											array(
													'controller'	=> 'entries',
													'action'			=> 'merge',
													$entry['Entry']['id'],
											),
											array('escape' => FALSE)
									);
								?>
							</li>
							<li class="divider"></li>
						<?php endif; ?>
						<li>
							<?php
								echo $this->Html->link(
										'<i class="icon-trash"></i>&nbsp;' . __('delete_tree_link'),
										array(
												'controller'	=> 'entries',
												'action'			=> 'delete',
												$entry['Entry']['id'],
										),
										array('escape' => FALSE),
										__('delete_tree_link_confirm_message')
								);
								$isModMenuPopulated = true;
							?>
						</li>
					</ul>
				</div><!-- /btn-group -->
			</div>
		</div>
		<?php $this->end('modMenu'); ?>
		<?php
			if ($isModMenuPopulated) {
				echo $this->fetch('modMenu');
				echo '<span style="margin-left:45px;"></span>';
			}
			// empty block or menu cascades in entries/mix
			$this->Blocks->set('modMenu', '');
		?>
	<?php endif; ?>
		&nbsp;
		&nbsp;
		<?php echo $this->element('entry/bookmark-link', array(
				'id' => $entry['Entry']['id'],
				'isBookmarked' => $entry['isBookmarked'],
				)); ?>

	</div>

	<?php endif; ?>
	<div class="a_b">
		<div id="posting_formular_slider_<?php echo $entry['Entry']['id']; ?>" class="posting_formular_slider" style="display:none;"  >
			<div class="spinner"></div>
		</div>
	</div> <!-- a_b -->
	<div id="posting_formular_slider_bottom_<?php echo $entry['Entry']['id']; ?>"></div>
</div>