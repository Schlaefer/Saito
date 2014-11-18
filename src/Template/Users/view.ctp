<?php

  use Cake\Utility\Text;

  $this->start('headerSubnavLeft');
  echo $this->Layout->navbarBack();
  $this->end();

  $this->element('users/menu');

  $urlToHistory = [
    'controller' => 'searches',
    'action' => 'advanced',
    '?' => ['name' => $user->get('username'), 'nstrict' => 1]
  ];

  $table = [
    [
      __('username_marking'),
      h($user->get('username')) . " <span class='infoText'>({$this->UserH->type($user->get('user_type'))})</span>",
      # @td user_type for mod and admin
    ],
    [
        __('user.set.avatar.t'),
        $this->UserH->getAvatar($user, ['link' => false]),
    ]
  ];

  if ($user->isLocked()) {
    $table[] = [
      __('user.set.lock.t'),
      $this->UserH->banned($user->get('user_lock')),
    ];
  }

  if ($user->get('user_real_name')) {
    $table[] = [
      __('user_real_name'),
      h($user->get('user_real_name'))
    ];
  }

  if ($user->get('user_email') && $user->get('personal_messages')) {
    $_contact = $this->UserH->contact($user);
    if ($CurrentUser->permission('saito.core.user.view.contact')) {
      $text = '(' . $this->Text->autoLinkEmails($user->get('user_email')) . ')';
      $_contact .= ' ' . $this->Layout->infoText($text);
    }
    $table[] = [__('Contact'), $_contact];
  }

  if ($user->get('user_hp')) {
    $table[] = [
      __('user_hp'),
      $this->UserH->homepage($user->get('user_hp'))
    ];
  }

  if ($user->get('user_place')) {
    $table[] = [
      __('user_place'),
      h($user->get('user_place'))
    ];
  }

  if ($SaitoSettings['map_enabled'] && $user->get('user_place_lat')) {
    $table[] = [
      '',
      $this->Map->map($user)
    ];
  }

  $table[] = [
    __('user_since'),
    strftime(__('date_short'),
      strtotime($user->get('registered')))
  ];

	$table[] = [
		__('user_postings'),
		$this->Html->link(
			$user->numberOfPostings(),
			$urlToHistory,
			['escape' => false]
		)
	];

  // helpful postings
  if ($user->get('solves_count')) {
    $table[] = [
      $this->Posting->solvedBadge(),
      $user->get('solves_count')
    ];
  }

  // ignoredBy
  $ignoreHelp = $this->SaitoHelp->icon(7);
  if ($user->get('ignore_count') > 0) {
    $table[] = [
      __('user_ignored_by'),
      $user->get('ignore_count') . $ignoreHelp
    ];
    $ignoreHelp = '';
  }

  // ignores
  if ($user->get('ignores')) {
    $o = [];
    foreach ($user->get('ignores') as $ignoredUser) {
      $ui = $this->Form->postLink(
        $this->Layout->textWithIcon('', 'eye-slash'),
        ['action' => 'unignore'],
        ['data' => ['id' => $ignoredUser->get('id')] , 'escape' => false]
      );
      $l = $this->UserH->linkToUserProfile($ignoredUser['User'], $CurrentUser);
      $o[] = "$ui &nbsp; $l";
    }
    $table[] = [
      __('user_ignores'),
      $this->Html->nestedList($o) . $ignoreHelp
    ];
  }

  // profile
  if ($user->get('profile')) {
    $table[] = [
      __('user_profile'),
      $this->Parser->parse($user->get('profile'))
    ];
  }

  if ($user->get('signature')) {
    $table[] = [
      __('user_signature'),
      $this->Parser->parse($user->get('signature'))
    ];
  }

	//= get additional profile info from plugins
  $items = $SaitoEventManager->dispatch(
    'Request.Saito.View.User.beforeFullProfile',
    [
      'user' => $user,
      'View' => $this
    ]
  );
  if ($items) {
    foreach ($items as $item) {
      $table[] = [$item['title'], $item['content']];
    }
  }
?>
<div class="users view">
  <div class="panel">
    <?= $this->Layout->panelHeading(__('user.b.profile')) ?>
    <div class="panel-content">
      <table class='table th-left elegant'>
        <?= $this->Html->tableCells($table); ?>
      </table>
    </div>

      <?php
      $isLoggedIn = $CurrentUser->isLoggedIn();
      $isUsersEntry = $CurrentUser->getId() == $user->get('id');

      $panel = '';
      if ($isUsersEntry) {
          $panel .= $this->Html->link(
              __('edit_userdata'),
              ['action' => 'edit', $user->get('id')],
              ['id' => 'btn_user_edit',
                  'class' => 'btn btn-submit panel-footer-form-btn']
          );
      }
      if ($isLoggedIn && !$isUsersEntry) {
          if ($CurrentUser->ignores($user->get('id'))) {
              $panel .= $this->Form->postLink(
                  $this->Layout->textWithIcon(h(__('unignore_this_user')), 'eye-slash'),
                  ['action' => 'unignore'],
                  [
                      'class' => 'btn panel-footer-form-btn shp',
                      'data' => ['id' => $user->get('id')],
                      'data-shpid' => 7,
                      'escape' => false
                  ]
              );
          } else {
              $panel .= $this->Form->postLink(
                  $this->Layout->textWithIcon(h(__('ignore_this_user')), 'eye-slash'),
                  ['action' => 'ignore'],
                  [
                      'class' => 'btn panel-footer-form-btn shp',
                      'data' => ['id' => $user->get('id')],
                      'data-shpid' => 7,
                      'escape' => false
                  ]
              );
          }

          $_menuItems = [];

          if ($CurrentUser->permission('saito.core.user.edit')) {
              // edit user
              $_menuItems[] = $this->Html->link(
                  '<i class="fa fa-pencil"></i> ' . __('Edit'),
                  ['action' => 'edit', $user->get('id')],
                  ['escape' => false]
              );
              $_menuItems[] = 'divider';

              // delete user
              $_menuItems[] = $this->Html->link(
                  '<i class="fa fa-trash-o"></i> ' . h(__('Delete')),
                  [
                      'prefix' => 'admin',
                      'controller' => 'Users',
                      'action' => 'delete',
                      $user->get('id'),
                  ],
                  ['escape' => false]
              );
          }
          if ($_menuItems) {
              $panel .= $this->Layout->dropdownMenuButton($_menuItems,
                  [
                      'class' => 'btnLink btn-icon panel-footer-form-btn',
                  ]);
          }
      }
      if ($panel): ?>
          <div class="panel-footer panel-form"><?= $panel ?></div>
      <?php endif; ?>
  </div>
  <div class="panel">
    <?= $this->Layout->panelHeading(__('user_recentposts')) ?>
    <div class="panel-content">
      <?php if (empty($lastEntries)): ?>
        <?=
        $this->element(
          'generic/no-content-yet',
          ['message' => __('No entries created yet.')]
        ); ?>
      <?php else: ?>
        <ul class="threadCollection-node root">
          <?php foreach ($lastEntries as $entry): ?>
            <li>
              <?= $this->Posting->renderThread($entry->toPosting(), ['ignore' => false]) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
    <?php if ($hasMoreEntriesThanShownOnPage) : ?>
      <div class="panel-footer panel-form">
        <?=
          $this->Html->link(__('Show all'),
            $urlToHistory,
            ['class' => 'panel-footer-form-bnt']) ?>
      </div>
    <?php endif; ?>
  </div>

  <?php
    if ($modLocking) { ?>
      <div class="panel">
        <?= $this->Layout->panelHeading(__('user.block.history')) ?>
        <div class="panel-content">
          <?= $this->element('users/block-report',
            ['UserBlock' => $user['UserBlock']]); ?>
        </div>
        <?php if (!$user->get('user_lock')) : ?>
          <div class="panel-footer panel-form">
            <?php
              $defaultValue = 86400;
              echo $this->Form->create(null, ['action' => 'lock']);
              echo $this->Form->submit(
                __('Block User'),
                ['div' => false, 'class' => 'btnLink']
              );
              echo "&nbsp;";
              echo $this->Form->input('lockRange', [
                'div' => ['style' => 'display: inline-block; margin: 0;'],
                'style' => 'vertical-align: middle;',
                'label' => false,
                'type' => 'range', 'min' => 21600, 'max' => 432000, 'value' => $defaultValue, 'step' => 21600
              ]);
              echo $this->Form->hidden('lockPeriod', ['value' => $defaultValue]);
              $this->Form->unlockField('User.lockPeriod');
              echo $this->Form->hidden('lockUserId', ['value' => $user->get('id')]);
              echo $this->Html->tag('span',
                Text::insert(__(':hours hours'), ['hours' => $defaultValue / 3600]),
                ['id' => 'lockTimeGauge', 'style' => 'padding: 0.5em']
              );
              echo $this->Form->end();
            ?>
            <script>
              SaitoApp.callbacks.afterAppInit.push(function() {
                require(['jquery', 'Backbone'], function($, Backbone) {
                  'use strict';
                  var BlockTimeGaugeView = Backbone.View.extend({
                    events: {'input #UserLockRange': '_onRangeChange'},
                    _onRangeChange: function(event) {
                      event.preventDefault();
                      var value = event.target.value;
                      var l10n = $.i18n.__(':hours hours', {hours: value/3600});
                      if (value === event.target.max) {l10n = '∞'; value = 0;}
                      this.$('#lockTimeGauge').html(l10n);
                      this.$('#UserLockPeriod').attr('value', value);
                    }
                  });
                  new BlockTimeGaugeView({el: '#UserLockForm'});
                });
              });
            </script>
          </div>
        <?php endif; ?>
      </div>
    <?php } ?>
</div>
