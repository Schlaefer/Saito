<?php
  $this->start('headerSubnavLeft');
  echo $this->Layout->navbarBack();
  $this->end();

  $this->element('users/menu');

  $urlToHistory = [
    'controller' => 'searches',
    'action' => 'advanced',
    '?' => ['name' => $user['User']['username'], 'nstrict' => 1]
  ];

  $table = [
    [
      __('username_marking'),
      h($user['User']['username']) . " <span class='infoText'>({$this->UserH->type($user['User']['user_type'])})</span>",
      # @td user_type for mod and admin
    ]
  ];

  if ($user['User']['user_lock']) {
    $table[] = [
      __('user.set.lock.t'),
      $this->UserH->banned($user['User']['user_lock']),
    ];
  }

  if (!empty($user['User']['user_real_name'])) {
    $table[] = [
      __('user_real_name'),
      h($user['User']['user_real_name'])
    ];
  }

  if (!empty($user['User']['user_email']) &&
    $user['User']['personal_messages'] == true
  ) {
    $_contact = $this->UserH->contact($user['User']);
    if ($CurrentUser->isAdmin()) {
      $_contact .= ' ' . $this->Layout->infoText(
          '(' .
          $this->Text->autoLinkEmails($user['User']['user_email']) .
          ')'
        );
    }
    $table[] = [__('Contact'), $_contact];
  }

  if (!empty($user['User']['user_hp'])) {
    $table[] = [
      __('user_hp'),
      $this->UserH->homepage($user['User']['user_hp'])
    ];
  }

  if (!empty($user['User']['user_place'])) {
    $table[] = [
      __('user_place'),
      h($user['User']['user_place'])
    ];
  }

  if (Configure::read('Saito.Settings.map_enabled') && !empty($user['User']['user_place_lat'])) {
    $table[] = [
      '',
      $this->Map->map($user)
    ];
  }

  $table[] = [
    __('user_since'),
    strftime(__('date_short'),
      strtotime($user['User']['registered']))
  ];

  $table[] = [
    __('user_postings'),
    $this->Html->link($user['User']['number_of_entries'],
      $urlToHistory,
      ['escape' => false])
  ];

  // helpful postings
  if (!empty($user['User']['solves_count'])) {
    $table[] = [
      $this->EntryH->solvedBadge(),
      $user['User']['solves_count']
    ];
  }

  // ignoredBy
  $ignoreHelp = $this->SaitoHelp->icon(7);
  if ($user['User']['ignore_count'] > 0) {
    $table[] = [
      __('user_ignored_by'),
      $user['User']['ignore_count'] . $ignoreHelp
    ];
    $ignoreHelp = '';
  }

  // ignores
  if (!empty($user['User']['ignores'])) {
    $o = [];
    foreach ($user['User']['ignores'] as $ignoredUser) {
      $ui = $this->Form->postLink(
        $this->Layout->textWithIcon('', 'eye-slash'),
        ['action' => 'unignore'],
        ['data' => ['id' => $ignoredUser['User']['id']] , 'escape' => false]
      );
      $l = $this->Layout->linkToUserProfile($ignoredUser['User'], $CurrentUser);
      $o[] = "$ui &nbsp; $l";
    }
    $table[] = [
      __('user_ignores'),
      $this->Html->nestedList($o) . $ignoreHelp
    ];
  }

  // profile
  if (!empty($user['User']['profile'])) {
    $table[] = [
      __('user_profile'),
      $this->Parser->parse($user['User']['profile'])
    ];
  }

  if (!empty($user['User']['signature'])) {
    $table[] = [
      __('user_signature'),
      $this->Parser->parse($user['User']['signature'])
    ];
  }

	//= get additional profile info from plugins
  $items = SaitoEventManager::getInstance()->dispatch(
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
      $isUsersEntry = $CurrentUser->getId() == $user['User']['id'];
      $isMod = $CurrentUser->isMod();
      if ($isLoggedIn || $isUsersEntry || $isMod):
        ?>
        <div class="panel-footer panel-form">
          <?php
            // default edit link
            if ($isUsersEntry) {
              echo $this->Html->link(
                __('edit_userdata'),
                ['action' => 'edit', $user['User']['id']],
                ['id' => 'btn_user_edit',
                  'class' => 'btn btn-submit panel-footer-form-btn']
              );
            }

            if ($isLoggedIn && !$isUsersEntry) {
              if ($CurrentUser->ignores($user['User']['id'])) {
								echo $this->Form->postLink(
									$this->Layout->textWithIcon(h(__('unignore_this_user')), 'eye-slash'),
									['action' => 'unignore'],
									[
										'class' => 'btn panel-footer-form-btn shp',
										'data' => ['id' => $user['User']['id']],
										'data-shpid' => 7,
										'escape' => false
									]
								);
              } else {
								echo $this->Form->postLink(
                  $this->Layout->textWithIcon(h(__('ignore_this_user')), 'eye-slash'),
                  ['action' => 'ignore'],
                  [
                    'class' => 'btn panel-footer-form-btn shp',
										'data' => ['id' => $user['User']['id']],
                    'data-shpid' => 7,
                    'escape' => false
                  ]
                );
              }
            }

            if ($isMod) {
              $_menuItems = [];

              if ($CurrentUser->isAdmin()) {
                // edit user
                $_menuItems[] = $this->Html->link(
                  '<i class="fa fa-pencil"></i> ' . __('Edit'),
                  array('action' => 'edit', $user['User']['id']),
                  array('escape' => false)
                );
                $_menuItems[] = 'divider';

                // delete user
                $_menuItems[] = $this->Html->link(
                  '<i class="fa fa-trash-o"></i> ' . __('Delete'),
                  array(
                    'controller' => 'users',
                    'action' => 'delete',
                    $user['User']['id'],
                    'admin' => true
                  ),
                  array('escape' => false)
                );
              }

              if (!empty($_menuItems)) {
                echo $this->Layout->dropdownMenuButton($_menuItems,
                  [
                    'class' => 'btnLink btn-icon panel-footer-form-btn',
                  ]);
              }
            }
          ?>
        </div>
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
              <?= $this->EntryH->renderThread($entry, ['ignore' => false]) ?>
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
    if ($CurrentUser->isAdmin() || $modLocking) { ?>
      <div class="panel">
        <?= $this->Layout->panelHeading(__('user.block.history')) ?>
        <div class="panel-content">
          <?= $this->element('users/block-report',
            ['UserBlock' => $user['UserBlock']]); ?>
        </div>
        <?php if (empty($user['User']['user_lock'])) : ?>
          <div class="panel-footer panel-form">
            <?php
              $defaultValue = 86400;
              echo $this->Form->create(['url' => ['controller' => 'users', 'action' => 'lock']]);
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
              echo $this->Form->hidden('lockUserId', ['value' => $user['User']['id']]);
              echo $this->Html->tag('span',
                CakeText::insert(__(':hours hours'), ['hours' => $defaultValue / 3600]),
                ['id' => 'lockTimeGauge', 'style' => 'padding: 0.5em']
              );
              echo $this->Form->end();
            ?>
            <script>
              SaitoApp.callbacks.afterAppInit.push(function() {
                require(['jquery', 'backbone'], function($, Backbone) {
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
