<?php
// setup
$level = $level ?? 0;
$editLinkIsShown = false;
$showSignature = false;

//data passed as json model
$jsEntry = json_encode(
    [
        'pid' => $entry->get('pid'),
        'isBookmarked' => $entry->isBookmarked(),
        'isSolves' => (bool)$entry->get('solves'),
        'rootEntryUserId' => (int)$rootEntry->get('user_id'),
        'time' => $this->TimeH->dateToIso($entry->get('time'))
    ]
);
?>
<div class="postingLayout js-entry-view-core" data-id="<?= $entry->get('id') ?>">
    <div class="postingLayout-main">
        <div class="postingLayout-aside">
            <div class="postingLayout-aside-item">
                <?= $this->User->getAvatar($entry->get('user')) ?>
            </div>
            <div class="postingLayout-aside-item">
                <?= $this->User->linkToUserProfile($entry->get('user'), $CurrentUser) ?>
            </div>
        </div>
        <div class="postingLayout-body">
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

    <?php if (!empty($showAnsweringPanel)) : ?>
        <div class="postingLayout-actions">
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
                $title = $this->Layout->textWithIcon(
                    __('forum_answer_linkname'),
                    'lock'
                );
                echo $this->Html->tag(
                    'span',
                    $title,
                    [
                        'class' => 'btn btn-primary',
                        'disabled' => 'disabled'
                    ]
                );
            } elseif (!$isAnsweringForbidden) {
                echo $this->Html->link(
                    __('forum_answer_linkname'),
                    '#',
                    ['class' => 'btn btn-primary js-btn-setAnsweringForm']
                );
            };

            if ($entry->isEditingAsUserAllowed()) {
                echo $this->Html->link(
                    __('edit_linkname'),
                    '/entries/edit/' . $entry->get('id'),
                    ['class' => 'btn btn-secondary js-btn-edit']
                );
            } elseif ($entry->isEditingAllowed()) {
            // edit entry
                $editLinkIsShown = true;
                $menuItems[] = $this->Html->link(
                    '<i class="fa fa-pencil"></i> ' . __('edit_linkname'),
                    '/entries/edit/' . $entry->get('id'),
                    ['class' => 'dropdown-item', 'escape' => false]
                );
            }

            if ($CurrentUser->permission('saito.core.posting.edit.restricted')) {
                // pin and lock thread
                if ($entry->isRoot()) {
                    if ($editLinkIsShown) {
                        $menuItems[] = 'divider';
                    }
                    $ajaxToggleOptions = [
                        'fixed' => 'fa fa-thumb-tack',
                        'locked' => 'fa fa-lock'
                    ];
                    foreach ($ajaxToggleOptions as $key => $icon) {
                        if (($entry->get($key) == 0)) {
                            $i10n = '_set_entry_link';
                        } else {
                            $i10n = '_unset_entry_link';
                        }
                        $title = __d('nondynamic', $key . $i10n);
                        $title = "<i class=\"$icon\"></i>&nbsp;$title";

                        $options = [
                            'class' => 'dropdown-item js-btn-toggle-' . $key,
                            'escape' => false
                        ];
                        $menuItems[] = $this->Html->link(
                            $title,
                            '#',
                            $options
                        );
                    }

                    $menuItems[] = 'divider';

                    // merge thread
                    $menuItems[] = $this->Html->link(
                        '<i class="fa fa-compress"></i>&nbsp;' . __(
                            'merge_tree_link'
                        ),
                        '/entries/merge/' . $entry->get('id'),
                        ['class' => 'dropdown-item', 'escape' => false]
                    );
                }

                // delete
                $menuItems[] = 'divider';
                $menuItems[] = $this->Html->link(
                    '<i class="fa fa-trash-o"></i>&nbsp;' . __('delete_tree_link'),
                    '#',
                    ['class' => 'dropdown-item js-delete', 'escape' => false]
                );
            }

            if (!empty($menuItems)) {
                echo $this->Layout->dropdownMenuButton(
                    $menuItems,
                    ['class' => 'btn btn-link']
                );
            }
            ?>
        </div>
    <?php endif; ?>

    <div class="postingLayout-slider"></div>
    <div class='js-data' data-entry='<?= $jsEntry ?>'></div>
</div>
