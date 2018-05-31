<?php
$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack(
    [
        'controller' => 'users',
        'action' => 'view',
        $user->get('id')
    ]
);
$this->end();
?>

<div class="user edit">
    <?= $this->Form->create($user); ?>
    <div class="panel">
    <?= $this->Layout->panelHeading($titleForPage, ['pageHeading' => true]) ?>
    <div class='panel-content panel-form'>
                <?php
                if ($CurrentUser->permission('saito.core.user.edit')) {
                    $cells = [
                        [
                            __('username_marking'),
                            $this->Form->control('username', ['label' => false])
                        ],
                        [
                            __('userlist_email'),
                            $this->Form->control('user_email', ['label' => false])
                        ],
                        [
                            __('user_type'),
                            $this->Form->radio(
                                'user_type',
                                [
                                    'user' => __('user.type.user'),
                                    'mod' => __('user.type.mod'),
                                    'admin' => __('user.type.admin'),
                                ],
                                ['legend' => false, 'separator' => '<br/>']
                            )
                        ]
                    ];
                } else {
                    $cells = [
                        [__('username_marking'), h($user->get('username'))],
                        [__('userlist_email'), h($user->get('user_email'))]
                    ];
                }

                if ($CurrentUser->isUser($user)) {
                    $cells[] = [
                        __('user_pw'),
                        $this->Html->link(
                            __('change_password_link'),
                            [
                                'action' => 'changepassword',
                                $user->get('id')
                            ]
                        )
                    ];
                }

                $avatar = $this->User->getAvatar($user, ['link' => false]);
                $avatarEditLink = $this->Html->link(
                    __('user.set.avatar.t'),
                    '/users/avatar/' . $user->get('id')
                );
                $avatarEdit = $this->Html->para(null, $avatarEditLink);
                $cells[] = [
                    __('user.avatar.t'),
                    $avatar . $avatarEdit
                ];

                $cells[] = [
                    __('user_real_name'),
                    $this->Form->control('user_real_name', ['label' => false]) .
                    $this->Html->para('exp', __('user_real_name_exp'))
                ];

                $cells[] = [
                    __('user_hp'),
                    $this->Form->control('user_hp', ['label' => false]) .
                    $this->Html->para('exp', __('user_hp_exp'))
                ];

                //= place and maps
                $cellContent = $this->Form->control(
                    'user_place',
                    ['label' => false]
                );
                $cellContent .= $this->Html->para('exp', __('user_place_exp'));

                $cells[] = [__('user_place'), $cellContent];

                //= user profile
                $cells[] = [
                    __('user_profile'),
                    $this->Form->control(
                        'profile',
                        ['rows' => '5', 'label' => false]
                    ) .
                    $this->Html->para('exp', __('user_profile_exp'))
                ];

                $cells[] = [
                    __('user_signature'),
                    $this->Form->control(
                        'signature',
                        ['rows' => '5', 'label' => false]
                    ) .
                    $this->Html->para('exp', __('user_signature_exp'))
                ];

                echo $this->Html->tag(
                    'table',
                    $this->Html->tableCells($cells),
                    ['class' => 'table th-left elegant']
                );
                ?>
        </div>
    </div>

    <div class="panel">
    <?= $this->Layout->panelHeading(__('Settings')) ?>
    <div class='panel-content panel-form'>
    <table class="table th-left elegant">

        <tr>
            <td> <?php echo __('user_sort_last_answer') ?> </td>
            <td>
                <?php
                echo $this->Form->radio(
                    'user_sort_last_answer',
                    [
                        '0' => __('user_sort_last_answer_time', 1),
                        '1' => __('user_sort_last_answer_last_answer', 1)
                    ],
                    [
                        'legend' => false,
                        'separator' => '<br/>',
                    ]
                );
                ?>
                <p class="exp"> <?php echo __('user_sort_last_answer_exp') ?> </p>
            </td>
        </tr>

        <tr>
            <td> <?php echo __('user_automaticaly_mark_as_read') ?> </td>
            <td>
                <?= $this->Form->checkbox('user_automaticaly_mark_as_read', ['label' => false ]); ?>
                <p class="exp">
                    <?php
                    echo __('user_automaticaly_mark_as_read_exp');
                    echo '&nbsp;';
                    echo $this->SaitoHelp->icon(2);
                    ?>
                </p>
            </td>
        </tr>

        <tr>
            <td> <?php echo __('user_signatures_hide') ?> </td>
            <td>
                <?php echo $this->Form->checkbox('user_signatures_hide'); ?> <p class="exp"> <?php echo __('user_signatures_hide_exp') ?> </p>
                <br/>
                <?php echo $this->Form->checkbox('user_signatures_images_hide'); ?> <p class="exp"> <?php echo __('user_signatures_images_hide_exp') ?> </p>
            </td>
        </tr>

        <tr>
            <td> <?php echo __('user_forum_refresh_time') ?> </td>
            <td>
                <?php
                echo $this->Form->control(
                    'user_forum_refresh_time',
                    [
                        'maxLength' => 3,
                        'label' => false,
                        'min' => 0,
                        'max' => 999,
                    ]
                );
                echo $this->Html->para('exp', __('user_forum_refresh_time_exp'))
                ?>
            </td>
        </tr>

        <?php if (count($availableThemes) > 1) : ?>
            <tr>
                <td> <?= __('user_theme') ?> </td>
                <td> <?=
                        $this->Form->control(
                            'user_theme',
                            [
                                'options' => $availableThemes,
                                'label' => false,
                                'val' => $currentTheme
                            ]
                        ) ?>
                    <p class="exp"> <?= __('user_theme_exp') ?> </p>
                </td>
            </tr>
        <?php endif; ?>

        <tr>
            <td> <?php echo __('user_colors') ?> </td>
            <td>
                <style>
                    .SpectrumColorpicker-theme-default.SpectrumColorpicker {
                        display: block;
                    }
                </style>
                <?=
                $this->SpectrumColorpicker->input(
                    'user_color_new_postings',
                    [
                        'label' => false,
                        'style' => 'height: auto; display: block; width: 100%'
                    ]
                )
                ?>
                <p class="exp"> <?php echo __('user_color_new_postings_exp') ?> </p>
                <br/>
                <?=
                $this->SpectrumColorpicker->input(
                    'user_color_old_postings',
                    [
                        'label' => false,
                        'style' => 'height: auto; display: block; width: 100%'
                    ]
                )
                ?>
                <p class="exp"> <?php echo __('user_color_old_postinings_exp') ?> </p>
                <br/>
                <?=
                $this->SpectrumColorpicker->input(
                    'user_color_actual_posting',
                    [
                        'label' => false,
                        'style' => 'height: auto; display: block; width: 100%'
                    ]
                )
                ?>
                <p class="exp"> <?php echo __('user_color_actual_posting_exp') ?> </p>
            </td>
        </tr>

        <tr>
            <td> <?php echo __('inline_view_on_click') ?> </td>
            <td>
                <?php echo $this->Form->checkbox('inline_view_on_click'); ?>
                <p class="exp"> <?= __('inline_view_on_click_exp') ?> </p>
            </td>
        </tr>
        <tr>
            <td> <?php echo __('user_show_thread_collapsed') ?> </td>
            <td>
                    <?php echo $this->Form->checkbox('user_show_thread_collapsed'); ?>
                    <p class="exp"> <?php echo __('user_show_thread_collapsed_exp') ?> </p>
            </td>
        </tr>

            <tr>
                <td> <?php echo __('user_pers_msg') ?> </td>
                <td> <?php echo $this->Form->checkbox('personal_messages'); ?> <p class="exp"> <?php echo __('user_pers_msg_exp') ?> </p></td>
            </tr>

            <?php if (!$SaitoSettings->get('category_chooser_global')
                && $SaitoSettings->get('category_chooser_user_override')
            ) : ?>
            <tr>
                <td>
                    <?php echo __('user_category_override') ?>
                </td>
                <td>
                    <?php echo $this->Form->checkbox('user_category_override'); ?>
                    <p class="exp">
                        <?php echo __('user_category_override_exp') ?>
                    </p>
                </td>
            </tr>
            <?php endif; ?>
    </table>
  </div> <!-- content -->
  </div>

    <?php
    //= get additional profile info from plugins
    $items = $SaitoEventManager->dispatch(
        'Request.Saito.View.User.edit',
        [
            'user' => $user,
            'View' => $this
        ]
    );
    if ($items) {
        foreach ($items as $item) {
            echo $item;
        }
    }
    ?>
    <br/>
    <?php
    echo $this->Form->submit(
        __('gn.btn.save.t'),
        ['id' => 'btn-primary', 'class' => 'btn btn-primary']
    );
    echo $this->Form->end();
    ?>
</div>
