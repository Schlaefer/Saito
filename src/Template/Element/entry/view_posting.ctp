<?php
	// setup
	SDV($level, 0);
	SDV($last_action, null);
	$editLinkIsShown = false;
	$showSignature = false;

	//data passed as json model
	$_jsEntry = json_encode([
		'pid' => $entry->get('pid'),
		'isBookmarked' => $entry->isBookmarked(),
		'isSolves' => (bool)$entry->get('solves'),
		'rootEntryUserId' => (int)$rootEntry->get('user_id'),
		'time' => $this->TimeH->mysqlTimestampToIso($entry->get('time'))
	]);
?>
<div class="postingLayout js-entry-view-core" data-id="<?php echo $entry->get('id') ?>">
    <div class="l-table">
        <div class="l-table-row">
            <div class="l-table-cell panel-info center">
                <?= $this->User->getAvatar($entry->get('user')) ?>
                <?= $this->User->linkToUserProfile($entry->get('user'), $CurrentUser) ?>
            </div>
            <div class="postingLayout-body panel-content l-table-cell-main">
                <?php
                if (!$CurrentUser->get('user_signatures_hide') &&
                    $entry->get('user')->get('signature') &&
                    !$entry->isNt()
                ) {
                    $showSignature = true;
                }
                echo $this->element(
                    '/entry/view_content',
                    [
                        'entry' => $entry,
                        'level' => $level,
                        'signature' => $showSignature
                    ]
                );
                ?>
            </div>
        </div>
    </div>

	<?php if (!empty($showAnsweringPanel)): ?>
		<div class="postingLayout-actions panel-footer panel-form">
			<div style="float:right">
				<?php
					//= get additional actions from plugins
					$items = $SaitoEventManager->dispatch(
						'Request.Saito.View.Posting.footerActions',
						[
							'posting' => $entry->toArray(),
							'View' => $this
						]
					);
					foreach ($items as $item) {
						echo $item;
					}
				?>
			</div>

            <?php
            $isAnsweringForbidden = $entry->isAnsweringForbidden();
            if ($isAnsweringForbidden === 'locked') {
                $title = $this->Layout->textWithIcon(__('forum_answer_linkname'),
                    'lock');
                echo $this->Html->tag('span', $title, [
                    'class' => 'btn btn-submit panel-footer-form-btn',
                    'disabled' => 'disabled'
                ]);
            } elseif (!$isAnsweringForbidden) {
                echo $this->Html->link(
                    __('forum_answer_linkname'),
                    '#',
                    ['class' => 'btn btn-submit js-btn-setAnsweringForm panel-footer-form-btn']
                );
            };
            ?>
			<?php if (!$entry->isEditingWithRoleUserForbidden()) : ?>
				<span class="small">
					<?= $this->Html->link(
                        __('edit_linkname'),
                        '/entries/edit/' . $entry->get('id'),
                        ['class' => 'btn btn-edit js-btn-edit panel-footer-form-btn']
                    ); ?>
				</span>
			<?php endif; ?>

            <?php
            // edit entry
            if (!$entry->isEditingAsCurrentUserForbidden()) {
                $editLinkIsShown = true;
                $_menuItems[] = $this->Html->link(
                    '<i class="fa fa-pencil"></i> ' . __('edit_linkname'),
                    '/entries/edit/' . $entry->get('id'),
                    ['escape' => false]
                );
            }

            if ($CurrentUser->permission('saito.core.posting.edit.restricted')) {
                // pin and lock thread
                if ($entry->isRoot()) {
                    if ($editLinkIsShown) {
                        $_menuItems[] = 'divider';
                    }
                    $ajaxToggleOptions = [
                        'fixed' => 'fa fa-thumb-tack',
                        'locked' => 'fa fa-lock'
                    ];
                    foreach ($ajaxToggleOptions as $key => $icon) {
                        $titleClass = 'title-toggle-' . $key;
                        if (($entry->get($key) == 0)) {
                            $i10n = '_set_entry_link';
                        } else {
                            $i10n = '_unset_entry_link';
                        }
                        $title = __d('nondynamic', $key . $i10n);
                        $title = "<i class=\"$icon\"></i>&nbsp;<span class=\"$titleClass\">$title</span>";

                        $options = [
                            'class' => 'btn-toggle-' . $key,
                            'escape' => false
                        ];
                        $_menuItems[] = $this->Html->link($title, '#',
                            $options);
                    }

                    $_menuItems[] = 'divider';

                    // merge thread
                    $_menuItems[] = $this->Html->link(
                        '<i class="fa fa-compress"></i>&nbsp;' . __('merge_tree_link'),
                        '/entries/merge/' . $entry->get('id'),
                        ['escape' => false]);
                }

                // delete
                $_menuItems[] = 'divider';
                $_menuItems[] = $this->Html->link(
                    '<i class="fa fa-trash-o"></i>&nbsp;' . __('delete_tree_link'),
                    '/entries/delete/' . $entry->get('id'),
                    array('escape' => false),
                    __('delete_tree_link_confirm_message')
                );
            }

            if (!empty($_menuItems)) {
                echo $this->Layout->dropdownMenuButton($_menuItems,
                    ['class' => 'btnLink btn-icon panel-footer-form-btn']);
            }
            ?>
		</div>
	<?php endif; ?>

	<div class="postingLayout-slider"></div>
	<div class='js-data' data-entry='<?= $_jsEntry ?>'></div>
</div>
