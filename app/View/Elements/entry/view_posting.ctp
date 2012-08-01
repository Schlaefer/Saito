<?php
	### setup ###
	if (!isset($level)) $level = 0;
	if (!isset($last_action)) $last_action = null;
  $editLinkIsShown = FALSE;
	###
?>
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
																'class' => 'btn btn-submit', 'accesskey' => "a" ,
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
							array ( 'class' => 'btn btn-edit', 'accesskey' => "e" )
							);
				?>
			</span>
		<?php endif; ?>

		<?php if( $CurrentUser->isMod()) : ?>
			<?php $isModMenuPopulated = false; ?>
			<?php $this->start('modMenu'); ?>
			&nbsp;
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
						<li>
							<?php
								echo $this->Ajax->link(
									($entry['Entry']['fixed'] == 0)
                    ? '<i class="icon-pushpin"></i>&nbsp;' . __('fixed_set_entry_link')
                    : '<i class="icon-pushpin"></i>&nbsp;' . __('fixed_unset_entry_link'),
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
                    'escape'  => FALSE,
									)
								);
							?>
						</li>
						<li>
							<?php
								echo $this->Ajax->link(
										($entry['Entry']['locked'] == 0)
                      ? '<i class="icon-lock"></i>&nbsp;' . __('locked_set_entry_link')
                      : '<i class="icon-unlock"></i>&nbsp;' . __('locked_unset_entry_link'),
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
                        'escape'  => FALSE,
										)
									);
							?>
						</li>
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
							?>
						</li>
					<?php endif; ?>
				</ul>
      </div><!-- /btn-group -->
		</div>
		<?php $this->end('modMenu'); ?>
		<?php
			if ($isModMenuPopulated) {
				echo $this->fetch('modMenu');
			}
		?>
	<?php endif; ?>

</div>

<?php endif; ?>
<div class="a_b">
	<div id="posting_formular_slider_<?php echo $entry['Entry']['id']; ?>" class="posting_formular_slider" style="display:none;"  >
		<div id="spinner_<?php echo $this->request->data['Entry']['id']; ?>" class="spinner"></div>
	</div>
</div> <!-- a_b -->
<div id="posting_formular_slider_bottom_<?php echo $entry['Entry']['id']; ?>"></div>