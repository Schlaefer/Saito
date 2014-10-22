<?php
	// setup
	SDV($level, 0);
	SDV($last_action, null);
	$editLinkIsShown = false;
	$_showSignature = false;

	//data passed as json model
	$_jsEntry = json_encode([
		'pid' => (int)$entry['Entry']['pid'],
		'isBookmarked' => $entry['isBookmarked'],
		'isSolves' => (bool)$entry['Entry']['solves'],
		'rootEntryUserId' => (int)$rootEntry['Entry']['user_id'],
		'time' => $this->TimeH->mysqlTimestampToIso($entry['Entry']['time'])
	]);
?>
<div class="postingLayout js-entry-view-core" data-id="<?php echo $entry['Entry']['id'] ?>">
	<div class="postingLayout-body panel-content">
		<?php
			if (!$CurrentUser['user_signatures_hide'] &&
					!empty($entry['User']['signature']) &&
					!$this->EntryH->isNt($entry)
			) {
				$_showSignature = true;
			}
			echo $this->element('/entry/view_content',
					[
							'entry' => $entry,
							'level' => $level,
							'signature' => $_showSignature
					]);
		?>
	</div>

	<?php if (!empty($showAnsweringPanel)): ?>
		<div class="postingLayout-actions panel-footer panel-form">
			<div style="float:right">
				<?php
					// flattr - Button
					if (Configure::read('Saito.Settings.flattr_enabled') == true
							// flattr is activated by admin
							&& $entry['Entry']['flattr'] == true
							&& !empty($entry['User']['flattr_uid'])
					) :
						echo $this->Flattr->button('',
								array(
										'uid' => $entry['User']['flattr_uid'],
										'language' => Configure::read('Saito.Settings.flattr_language'),
										'title' => $entry['Entry']['subject'],
										'description' => $entry['Entry']['subject'],
										'cat' => Configure::read('Saito.Settings.flattr_category'),
										'button' => 'compact',
								)
						);
					endif;
				?>
			</div>

			<?php
				# @td MCV
				$answering_forbidden = $entry['rights']['isAnsweringForbidden'];
				if ($answering_forbidden === 'locked') {

					echo $this->Html->tag(
							'span',
							$this->Layout->textWithIcon(__('forum_answer_linkname'), 'lock'),
							[
									'class' => 'btn btn-submit panel-footer-form-btn',
									'disabled' => 'disabled'
							]
					);
				} elseif (!$answering_forbidden) {
					echo $this->Html->link(
							__('forum_answer_linkname'),
							'#',
							[
									'class' => 'btn btn-submit js-btn-setAnsweringForm panel-footer-form-btn',
									'accesskey' => "a",
							]
					);
				};
			?>
			<?php if (isset($entry['rights']['isEditingAsUserForbidden']) &&
					!$entry['rights']['isEditingAsUserForbidden']
			) : ?>
				<span class="small">
					<?=
						$this->Html->link(
								__('edit_linkname'),
								'/entries/edit/' . $entry['Entry']['id'],
								['class' => 'btn btn-edit js-btn-edit panel-footer-form-btn', 'accesskey' => 'e']
						);
					?>
				</span>
			<?php endif; ?>

			<?php
				// mod menu
				if ($CurrentUser->isMod()) {
					// edit entry
					if (isset($entry['rights']['isEditingForbidden']) &&
							($entry['rights']['isEditingForbidden'] == false)
					) {
						$editLinkIsShown = true;
						$_menuItems[] = $this->Html->link(
								'<i class="fa fa-pencil"></i> ' . __('edit_linkname'),
								'/entries/edit/' . $entry['Entry']['id'],
								['escape' => false]
						);
					}

					// pin and lock thread
					if ($entry['Entry']['pid'] == 0) {
						if ($editLinkIsShown) {
							$_menuItems[] = 'divider';
						}
						$_ajaxToggleOptions = [
								'fixed' => 'fa fa-thumb-tack',
								'locked' => 'fa fa-lock'
						];
						foreach ($_ajaxToggleOptions as $key => $icon) {
							$_menuItems[] = $this->Js->link(
									'<i class="' . $icon . '"></i>&nbsp;'
									. '<span id="title-entry_' . $key . '-' . $entry['Entry']['id'] . '">'
									. (($entry['Entry'][$key] == 0) ? __d('nondynamic',
											$key . '_set_entry_link') : __d('nondynamic',
											$key . '_unset_entry_link'))
									. '</span>',
									'/entries/ajax_toggle/' . $entry['Entry']['id'] . '/' . $key,
									array(
											'id' => 'btn-entry_' . $key . '-' . $entry['Entry']['id'],
											'success' => "$('#title-entry_{$key}-{$entry['Entry']['id']}').html(data);",
											'buffer' => false,
											'escape' => false,
									)
							);
							unset($_ajaxToggleOptions);
						}

						$_menuItems[] = 'divider';

						// merge thread
						$_menuItems[] = $this->Html->link(
								'<i class="fa fa-compress"></i>&nbsp;' . __('merge_tree_link'),
								'/entries/merge/' . $entry['Entry']['id'],
								array('escape' => false));

					}

					// delete
					$_menuItems[] = 'divider';
					$_menuItems[] = $this->Html->link(
							'<i class="fa fa-trash-o"></i>&nbsp;' . __('delete_tree_link'),
							'/entries/delete/' . $entry['Entry']['id'],
							array('escape' => false),
							__('delete_tree_link_confirm_message')
					);

					echo $this->Layout->dropdownMenuButton($_menuItems,
							['class' => 'btnLink btn-icon panel-footer-form-btn']);
				}

				$items = SaitoEventManager::getInstance()->dispatch(
					'Request.Saito.View.Posting.footerItems',
					[
						'posting' => $entry,
						'View' => $this
					]
				);
				if ($items) {
					foreach ($items as $item) {
						echo $item;
					}
				}
			?>
		</div>
	<?php endif; ?>

	<div class="postingLayout-slider"></div>
	<div class='js-data' data-entry='<?= $_jsEntry ?>'></div>
</div>
