<?php
	// setup
	if (!isset($level)) { $level = 0; }
	if (!isset($last_action)) { $last_action = null; }
	$editLinkIsShown = false;

	//data passed as json model
	$_jsEntry = json_encode([
		'pid' => (int)$entry['Entry']['pid'],
		'isBookmarked' => $entry['isBookmarked'],
		'isSolves' => (bool)$entry['Entry']['solves'],
		'rootEntryUserId' => (int)$rootEntry['Entry']['user_id'],
		'time' => $this->TimeH->mysqlTimestampToIso($entry['Entry']['time'])
	]);
?>
<div class="js-entry-view-core" data-id="<?php echo $entry['Entry']['id'] ?>">
	<div class="content">
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

	</div>
	<?php if (!empty($showAnsweringPanel)): ?>
		<div class="l-box-footer box-footer-form">
			<div style="float:right">
				<?php
					// flattr - Button
					if (Configure::read('Saito.Settings.flattr_enabled') == TRUE
							// flattr is activated by admin
							&& $entry['Entry']['flattr'] == TRUE
							&& !empty($entry['User']['flattr_uid'])
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
						class="fa fa-lock fa-huge shp shp-right"
						data-title="<?php echo __('Help'); ?>"
						data-content="<?php echo __('answering_forbidden_locked_shp'); ?>"
						></i>
				<?php
				} elseif (!$answering_forbidden) {
					echo $this->Html->link(
						__('forum_answer_linkname'),
						'#',
						array(
							'class' => 'btn btn-submit js-btn-setAnsweringForm',
							'accesskey' => "a",
						)
					);
				};
			?>
			<?php if (isset($entry['rights']['isEditingAsUserForbidden']) &&
					!$entry['rights']['isEditingAsUserForbidden']
			) : ?>
				&nbsp;
				<span class="small">
					<?=
						$this->Html->link(
							__('edit_linkname'),
								'/entries/edit/' . $entry['Entry']['id'],
							['class' => 'btn btn-edit js-btn-edit', 'accesskey' => 'e']
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
							<i class="fa fa-wrench"></i>
							&nbsp;
							<i class="fa fa-caret-down"></i>
							</button>
						<ul class="dropdown-menu">
						<?php
							if (isset($entry['rights']['isEditingForbidden']) && ($entry['rights']['isEditingForbidden'] == false)) :
								$isModMenuPopulated = true;
								$editLinkIsShown = TRUE;
							?>
							<li>
								<?php echo $this->Html->link(
												'<i class="fa fa-pencil"></i> ' . __('edit_linkname'),
												'/entries/edit/' . $entry['Entry']['id'],
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
										'fixed' => 'fa fa-thumb-tack',
										'locked' => 'fa fa-lock'
								);
								foreach($ajax_toggle_options as $key => $icon):
										echo '<li>';
										echo $this->Js->link(
											'<i class="'.$icon.'"></i>&nbsp;'
											. '<span id="title-entry_' . $key . '-' . $entry['Entry']['id'] . '">'
											. (($entry['Entry'][$key] == 0) ? __d('nondynamic', $key . '_set_entry_link') : __d('nondynamic', $key . '_unset_entry_link'))
											. '</span>',
											'/entries/ajax_toggle/' .	$entry['Entry']['id'] . '/' . $key,
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
											'<i class="fa fa-compress"></i>&nbsp;' . __('merge_tree_link'),
											'/entries/merge/' . $entry['Entry']['id'],
											array('escape' => FALSE)
									);
								?>
							</li>
							<li class="divider"></li>
						<?php endif; ?>
						<li>
							<?php
								echo $this->Html->link(
										'<i class="fa fa-trash-o"></i>&nbsp;' . __('delete_tree_link'),
										'/entries/delete/' . $entry['Entry']['id'],
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
	 	endif;
		?>
		<span class="js-buttons"></span>
	</div>
	<?php endif; ?>
	<div class="posting_formular_slider" style="display:none;"></div>
	<div class='js-data' data-entry='<?= $_jsEntry ?>'></div>
</div>