<?php

use Saito\User\Permission\ResourceAI;

// setup
$level = $level ?? 0;
$editLinkIsShown = false;
$showSignature = false;

//data passed as json model
$jsEntry = json_encode(
    [
        'pid' => $entry->get('pid'),
        'isBookmarked' => $entry->isBookmarked(),
        'solves' => $entry->get('solves'),
        'showSolvedBtn' => $CurrentUser->permission(
            'saito.core.posting.solves.set',
            (new ResourceAI())->onRole($rootEntry->get('user')->getRole())->onOwner($rootEntry->get('user_id'))
        ),
        'tid' => (int)$entry->get('tid'),
        'time' => $this->TimeH->dateToIso($entry->get('time')),
    ]
);
?>
<div class="postingLayout js-entry-view-core" data-id="<?= $entry->get('id') ?>">
    <div class="postingLayout-main">
        <div class="postingLayout-aside">
            <div class="postingLayout-aside-item">
                <?= $this->User->getAvatar($entry->get('user')) ?>
            </div>
        </div>
        <div class="postingLayout-body">
            <?php
            if (
                !$CurrentUser->get('user_signatures_hide') &&
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
                    'signature' => $showSignature,
                ]
            );
            ?>
        </div>
        <div class="postingLayout-slider"></div>
    </div>

    <?php if (!empty($showAnsweringPanel)) : ?>
        <div class="postingLayout-actions">
            <div style="float:right">
                <?php
                //= get additional actions from plugins
                $items = $SaitoEventManager->dispatch(
                    'saito.core.posting.view.footerActions.request',
                    [
                        'posting' => $entry->toArray(),
                        'View' => $this,
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
                        'disabled' => 'disabled',
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
                    '<i class="fa fa-fw fa-pencil"></i> ' . __('edit_linkname'),
                    '/entries/edit/' . $entry->get('id'),
                    ['class' => 'dropdown-item', 'escape' => false]
                );
            }

            /// pin and lock thread
            if ($entry->isRoot()) {
                if (!empty($menuItems)) {
                    $menuItems[] = 'divider';
                }

                if ($CurrentUser->permission('saito.core.posting.pinAndLock')) {
                    $ajaxToggleOptions = [
                        'fixed' => 'fa fa-fw fa-thumb-tack',
                        'locked' => 'fa fa-fw fa-lock',
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
                            'escape' => false,
                        ];
                        $menuItems[] = $this->Html->link(
                            $title,
                            '#',
                            $options
                        );
                    }
                }

                if ($CurrentUser->permission('saito.core.posting.merge')) {
                    if (!empty($menuItems)) {
                        $menuItems[] = 'divider';
                    }
                    // merge thread
                    $menuItems[] = $this->Html->link(
                        '<i class="fa fa-fw fa-compress"></i>&nbsp;' . h(__('merge_tree_link')),
                        '/entries/merge/' . $entry->get('id'),
                        ['class' => 'dropdown-item', 'escape' => false]
                    );
                }
            }

            if ($CurrentUser->permission('saito.core.posting.delete')) {
                // delete
                if (!empty($menuItems)) {
                    $menuItems[] = 'divider';
                }
                $menuItems[] = $this->Html->link(
                    '<i class="fa fa-fw fa-trash-o"></i>&nbsp;' . h(__('delete_tree_link')),
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

    <div class='js-data' data-entry='<?= $jsEntry ?>'></div>
</div>
