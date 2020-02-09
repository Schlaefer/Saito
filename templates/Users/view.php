<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 * @var \App\View\AppView $this
 */

use Saito\User\Permission\ResourceAI;

$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack();
$this->end();

$this->element('users/menu');

$ResourceAI = (new ResourceAI())->onRole($user->getRole())->onOwner($user->getId());

$urlToHistory = [
    'controller' => 'searches',
    'action' => 'advanced',
    '?' => ['name' => $user->get('username')],
];

$role = $this->Permissions->roleAsString($user->getRole());
$table = [
    [
        __('username_marking'),
        h($user->get('username')) . " <span class='infoText'>({$role})</span>",
    ],
];

$table[] = [
    __('user.avatar.t'),
    $this->User->getAvatar($user, ['link' => false]),
];

if (!$user->isActivated() && $CurrentUser->permission('saito.core.user.activate.view')) {
    $table[] = [
        h(__('user.actv.t')),
        h(__('user.actv.ny')),
    ];
}

if ($user->isLocked()) {
    $table[] = [
        __('user.set.lock.t'),
        $this->User->banned(true),
    ];
}

if ($user->get('user_real_name')) {
    $table[] = [
        __('user_real_name'),
        h($user->get('user_real_name')),
    ];
}

$viewContactPermission = $CurrentUser->permission('saito.core.user.contact');
if ($user->get('user_email') && ($user->get('personal_messages') || $viewContactPermission)) {
    $concat = $this->Html->link(
        '<i class="fa fa-envelope-o fa-lg"></i>',
        ['controller' => 'contacts', 'action' => 'user', $user['id']],
        ['escape' => false]
    );
    if ($viewContactPermission) {
        $text = '(' . $this->Text->autoLinkEmails($user->get('user_email')) . ')';
        $concat .= ' ' . $this->Layout->infoText($text);
    }
    $table[] = [__('Contact'), $concat];
}

if ($user->get('user_hp')) {
    $table[] = [
        __('user_hp'),
        $this->User->linkExternalHomepage($user->get('user_hp')),
    ];
}

if ($user->get('user_place')) {
    $table[] = [
        __('user_place'),
        h($user->get('user_place')),
    ];
}

$table[] = [
    __('user_since'),
    $this->TimeH->formatTime(
        $user->get('registered'),
        '%d.%m.%Y'
    ),
];

if ($CurrentUser->permission('saito.core.user.lastLogin.view')) {
    $table[] = [
        h(__('user.lastLogin.t')),
        empty($user->get('last_login'))
            ? __('user.lastLogin.never')
            : $this->TimeH->formatTime($user->get('last_login')),
    ];
}

$table[] = [
    __('user_postings'),
    $this->Html->link(
        $user->numberOfPostings(),
        $urlToHistory,
        ['escape' => false]
    ),
];

// helpful postings
if ($solved) {
    $table[] = [
        $this->Posting->solvedBadge(),
        $solved,
    ];
}

// ignoredBy
$ignoreHelp = $this->SaitoHelp->icon(7);
if ($user->get('ignore_count') > 0) {
    $table[] = [
        __('user_ignored_by'),
        $user->get('ignore_count') . $ignoreHelp,
    ];
    $ignoreHelp = '';
}

// ignores
if ($user->get('ignores') && count($user->get('ignores')->toArray())) {
    $o = [];
    foreach ($user->get('ignores') as $ignoredUser) {
        $ui = $this->Form->postLink(
            $this->Layout->textWithIcon('', 'eye-slash'),
            ['action' => 'unignore'],
            ['data' => ['id' => $ignoredUser->get('id')], 'escape' => false]
        );
        $l = $this->User->linkToUserProfile(
            $ignoredUser,
            $CurrentUser
        );
        $o[] = "$ui &nbsp; $l";
    }
    $table[] = [
        __('user_ignores'),
        $this->Html->nestedList($o) . $ignoreHelp,
    ];
}

// profile
if ($user->get('profile')) {
    $table[] = [
        __('user_profile'),
        $this->Parser->parse($user->get('profile')),
    ];
}

if ($user->get('signature')) {
    $table[] = [
        __('user_signature'),
        $this->Parser->parse($user->get('signature'), ['embed' => false]),
    ];
}

//= get additional profile info from plugins
$items = $SaitoEventManager->dispatch(
    'Request.Saito.View.User.beforeFullProfile',
    ['user' => $user, 'View' => $this]
);
if ($items) {
    foreach ($items as $item) {
        $table[] = [$item['title'], $item['content']];
    }
}

?>
<div class="users view">
    <div class="card mb-3">
        <div class="card-header">
            <?= $this->Layout->panelHeading(__('user.b.profile')) ?>
        </div>
        <div class="card-body">
            <table class='table th-left elegant'>
                <?= $this->Html->tableCells($table); ?>
            </table>
        </div>

        <?php
        $panel = '';

        if ($CurrentUser->permission('saito.core.user.edit', $ResourceAI)) {
            $panel .= $this->Html->link(
                __('edit_userdata'),
                ['action' => 'edit', $user->get('id')],
                [
                    'id' => 'btn_user_edit',
                    'class' => 'btn btn-primary',
                ]
            );
        }
        if (!$CurrentUser->isUser($user)) {
            // START User ignore
            if ($CurrentUser->ignores($user->get('id'))) {
                $panel .= $this->Form->postLink(
                    $this->Layout->textWithIcon(
                        h(__('unignore_this_user')),
                        'eye-slash'
                    ),
                    ['action' => 'unignore'],
                    [
                        'class' => 'btn shp',
                        'data' => ['id' => $user->get('id')],
                        'data-shpid' => 7,
                        'escape' => false,
                    ]
                );
            } else {
                $panel .= $this->Form->postLink(
                    $this->Layout->textWithIcon(
                        h(__('ignore_this_user')),
                        'eye-slash'
                    ),
                    ['action' => 'ignore'],
                    [
                        'class' => 'btn shp',
                        'data' => ['id' => $user->get('id')],
                        'data-shpid' => 7,
                        'escape' => false,
                    ]
                );
            }
            // END User ignore
        }

        // START Admin menu
        $menuItems = [];

        $deleteAllowed = !$CurrentUser->isUser($user) && $CurrentUser->permission('saito.core.user.delete', $ResourceAI);
        if ($deleteAllowed) {
            if (!empty($menuItems)) {
                $menuItems[] = 'divider';
            }
            ?>

            <?php
            // delete user
            $menuItems[] = $this->Html->link(
                '<i class="fa fa-fw fa-trash-o"></i> ' . h(__('Delete')),
                '#',
                [
                    'class' => 'dropdown-item',
                    'escape' => false,
                    'onclick' => "event.preventDefault(); $('#deleteUserModal').modal('show');",
                ]
            );
        }

        if ($menuItems) {
            $panel .= $this->Layout->dropdownMenuButton(
                $menuItems,
                ['class' => 'btn btn-link']
            );
        }
        // END Admin menu
        if ($panel) { ?>
            <div class="card-footer"><?= $panel ?></div>
            <?php
        }
        ?>
    </div>
    <?php
    if ($CurrentUser->permission('saito.core.user.lock.set', $ResourceAI)) { ?>
        <div class="card mb-3">
            <div class="card-header">
                <?= $this->Layout->panelHeading(__('user.block.history')) ?>
            </div>
            <div class="card-body">
                <?=
                $this->element(
                    'users/block-report',
                    ['UserBlock' => $user->get('user_blocks')]
                );
                ?>
            </div>
            <?php
            if (!$user->get('user_lock')) {
                $defaultValue = 86400;
                $lock[] = $this->Form
                    ->create(
                        $blockForm,
                        ['url' => ['action' => 'lock'], 'id' => 'blockForm']
                    );
                $lock[] = $this->Form->button(
                    __('Block User'),
                    [
                        'class' => 'btn btn-link',
                        'type' => 'submit',
                    ]
                );
                $lock[] = "&nbsp;";
                $lock[] = $this->Form->control(
                    'lockRange',
                    [
                        'templates' => ['inputContainer' => '{{content}}'],
                        'label' => false,
                        'max' => 432000,
                        'min' => 21600,
                        'step' => 21600,
                        'style' => 'vertical-align: middle;',
                        'type' => 'range',
                        'value' => $defaultValue,
                    ]
                );
                $lock[] = $this->Form->hidden(
                    'lockPeriod',
                    ['id' => 'lockPeriod', 'value' => $defaultValue]
                );
                $lock[] = $this->Form->unlockField('lockPeriod');
                $lock[] = $this->Form->hidden(
                    'lockUserId',
                    ['value' => $user->get('id')]
                );
                $lock[] = $this->Html->tag(
                    'span',
                    __('user.block.hours', ['hours' => $defaultValue / 3600]),
                    ['id' => 'lockTimeGauge', 'style' => 'padding: 0.5em']
                );
                $lock[] = $this->Form->end();
                ?>
                <div class="card-footer">
                    <?= implode('', $lock) ?>
                    <script>
                        SaitoApp.callbacks.afterAppInit.push(function () {
                            var BlockTimeGaugeView = Marionette.View.extend({
                                events: {'input #lockrange': '_onRangeChange'},
                                _onRangeChange: function (event) {
                                    var value = event.target.value;
                                    var l10n = $.i18n.__('user.block.hours', {hours: value / 3600});
                                    event.preventDefault();
                                    if (value === event.target.max) {
                                        l10n = '∞';
                                        value = 0;
                                    }
                                    this.$('#lockTimeGauge').html(l10n);
                                    this.$('#lockPeriod').attr('value', value);
                                }
                            });
                            new BlockTimeGaugeView({el: '#blockForm'});
                        });
                    </script>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    }
    ?>
</div>

<?php if ($deleteAllowed) : ?>
<div id="deleteUserModal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <?= $this->Form->create(null, ['method' => 'POST', 'url' => ['action' => 'delete', $user->getId()]]) ?>
        <div class="modal-header bg-danger text-white">
            <h5 class="modal-title">
                <?= __('user.del.exp.1', h($user->get('username'))) ?>
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <div class="alert alert-error">
                <ul>
                    <li>
                        <?= __('user.del.exp.2') ?>
                    </li>
                    <li>
                        <?= __('user.del.exp.3') ?>
                    </li>
                    <li>
                        <?= __('user.del.exp.4') ?>
                    </li>
                </ul>
            </div>
            <div class="form-group form-check">
                <?= $this->Form->control(
                    'userdeleteconfirm',
                    [
                        'class' => 'form-input mr-1',
                        'label' => __('user.del.confirm'),
                        'required' => true,
                        'type' => 'checkbox',
                    ]
                )?>
            </div>
        </div>
        <div class="modal-footer">
            <?php
            $this->Form->setTemplates(['submitContainer' => '{{content}}']);
            echo $this->Form->submit(
                __('user.del.btn.t'),
                ['class' => 'btn btn-danger']
            );
            echo ' ';
            echo $this->Form->button(
                __('Cancel'),
                ['class' => 'btn', 'data-dismiss' => 'modal']
            );
            ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
  </div>
</div>
<?php endif; ?>

<script type="text/template" id="tpl-recentposts">
    <div class="panel">
        <div class="panel-content">
            <?php
            if (empty($lastEntries)) {
                $this->element(
                    'generic/no-content-yet',
                    ['message' => __('No entries created yet.')]
                );
            } else {
                $threads = [];
                foreach ($lastEntries as $entry) {
                    $threads[] = $this->Posting->renderThread(
                        $entry,
                        ['ignore' => false]
                    );
                }
                echo $this->Html->nestedList(
                    $threads,
                    ['class' => 'threadCollection-node root']
                );
            }
            ?>
        </div>
        <?php
        if ($hasMoreEntriesThanShownOnPage) {
            $panel = $this->Html->link(
                __('Show all'),
                $urlToHistory,
                ['class' => 'panel-footer-form-bnt']
            );

            echo $this->Html->div('', $panel);
        }
        ?>
    </div>
</script>
<?php
    $userData = ['id' => $user->get('id')];
    $permissions = [
        'saito.plugin.uploader.add',
        'saito.plugin.uploader.delete',
        'saito.plugin.uploader.view',
    ];
    foreach ($permissions as $permission) {
        $userData['permission'][$permission] = $CurrentUser->permission($permission, $ResourceAI);
    }
    ?>
<div class="js-rgUser" data-user="<?= h(json_encode($userData)) ?>"></div>
