<?php

use Saito\User\Permission\ResourceAI;

$this->start('headerSubnavLeft');
echo $this->Layout->navbarBack(
    [
        'controller' => 'users',
        'action' => 'view',
        $user->get('id'),
    ]
);
$this->end();
?>

<div class="user edit card panel-center">
    <?= $this->Form->create($user); ?>
    <div class='panel-content panel-form card-body'>
        <h2 class="text-center"><?= __('Profile') ?></h2>
        <?php
        $cells = [];

        if ($CurrentUser->permission('saito.core.user.name.set', (new ResourceAI())->onRole($user->getRole())->onOwner($user->getId()))) {
            $cells[] = [
                __('username_marking'),
                $this->Form->control('username', ['class' => 'form-control', 'label' => false]),
            ];
        } else {
            $cells[] = [__('username_marking'), h($user->get('username'))];
        }

        if ($CurrentUser->permission('saito.core.user.email.set', (new ResourceAI())->onRole($user->getRole())->onOwner($user->getId()))) {
            $cells[] = [
                __('userlist_email'),
                $this->Form->control('user_email', ['class' => 'form-control', 'label' => false]),
            ];
        } else {
            $cells[] = [__('userlist_email'), h($user->get('user_email'))];
        }

        $idP = (new ResourceAI())->onRole($user->getRole());
        if (
            $CurrentUser->permission('saito.core.user.role.set.restricted', $idP)
            || $CurrentUser->permission('saito.core.user.role.set.unrestricted', $idP)
        ) {
            $cells[] = [
                __('user_type'),
                $this->Html->para(null, $this->Permissions->roleAsString($user->getRole())) .
                $this->Html->para(
                    null,
                    $this->Html->link(
                        __('user.role.set.btn'),
                        ['action' => 'role', $user->get('id')]
                    )
                ),
            ];
        } else {
            $cells[] = [__('user_type'), $this->Permissions->roleAsString($user->getRole())];
        }

        if ($user->isUser($CurrentUser)) {
            $cells[] = [
                __('user_pw'),
                $this->Html->link(
                    __('change_password_link'),
                    [
                        'action' => 'changepassword',
                        $user->get('id'),
                    ]
                ),
            ];
        } elseif ($CurrentUser->permission('saito.core.user.password.set')) {
            $cells[] = [
                __('user_pw'),
                $this->Html->link(
                    __('user.pw.set.btn'),
                    ['action' => 'setpassword', $user->get('id')]
                ),
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
            $avatar . $avatarEdit,
        ];

        $cells[] = [
            __('user_real_name'),
            $this->Form->control('user_real_name', ['class' => 'form-control', 'label' => false]) .
            $this->Html->para('exp', __('user_real_name_exp')),
        ];

        $cells[] = [
            __('user_hp'),
            $this->Form->control('user_hp', ['class' => 'form-control', 'label' => false]) .
            $this->Html->para('exp', __('user_hp_exp')),
        ];

        //= place and maps
        $cellContent = $this->Form->control(
            'user_place',
            ['class' => 'form-control', 'label' => false]
        );
        $cellContent .= $this->Html->para('exp', __('user_place_exp'));

        $cells[] = [__('user_place'), $cellContent];

        //= user profile
        $cells[] = [
            __('user_profile'),
            $this->Form->control(
                'profile',
                ['class' => 'form-control', 'rows' => '5', 'label' => false]
            ) .
            $this->Html->para('exp', __('user_profile_exp')),
        ];

        $cells[] = [
            __('user_signature'),
            $this->Form->control(
                'signature',
                ['class' => 'form-control', 'rows' => '5', 'label' => false]
            ) .
            $this->Html->para('exp', __('user_signature_exp')),
        ];

        echo $this->Html->tag(
            'table',
            $this->Html->tableCells($cells),
            ['class' => 'table th-left elegant']
        );
        ?>

        <hr>
        <h2 class="text-center pt-3"><?= __('Settings') ?></h2>

        <table class="table th-left elegant">
            <tr>
                <td> <?php echo __('user_sort_last_answer') ?> </td>
                <td>
                    <?= $this->Form->control(
                        'user_sort_last_answer',
                        [
                            'class' => 'ml-3 mr-1',
                            'label' => false,
                            'options' => [
                                '0' => __('user_sort_last_answer_time', 1),
                                '1' => __('user_sort_last_answer_last_answer', 1),
                            ],
                            'type' => 'radio',
                        ]
                    ) ?>
                    <p class="exp"> <?php echo __('user_sort_last_answer_exp') ?> </p>
                </td>
            </tr>

            <tr>
                <td> <?php echo __('user_automaticaly_mark_as_read') ?> </td>
                <td>
                    <div class="form-group form-check">
                        <?= $this->Form->control(
                            'user_automaticaly_mark_as_read',
                            [
                                'type' => 'checkbox',
                                'class' => 'form-check-input',
                                'label' => [
                                    'class' => 'form-check-label',
                                    'text' => __('user_automaticaly_mark_as_read_exp'),
                                ],
                            ]
                        )
                        ?>
                        <p>
                            <?= $this->SaitoHelp->icon(2) ?>
                        </p>
                    </div>
                </td>
            </tr>

            <tr>
                <td> <?php echo __('user_signatures_hide') ?> </td>
                <td>
                    <div class="form-group form-check">
                        <?= $this->Form->control(
                            'user_signatures_hide',
                            [
                                'type' => 'checkbox',
                                'class' => 'form-check-input',
                                'label' => [
                                    'class' => 'form-check-label',
                                    'text' => __('user_signatures_hide_exp'),
                                ],
                            ]
                        )
                        ?>
                    </div>
                    <div class="form-group form-check">
                        <?= $this->Form->control(
                            'user_signatures_images_hide',
                            [
                                'type' => 'checkbox',
                                'class' => 'form-check-input',
                                'label' => [
                                    'class' => 'form-check-label',
                                    'text' => __('user_signatures_images_hide_exp'),
                                ],
                            ]
                        )
?>
                    </div>
                </td>
            </tr>

            <tr>
                <td> <?php echo __('user_forum_refresh_time') ?> </td>
                <td>
                    <?php
                    echo $this->Form->control(
                        'user_forum_refresh_time',
                        [
                            'class' => 'form-control',
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
                                    'val' => $currentTheme,
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
                            'style' => 'height: auto; display: block; width: 100%',
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
                            'style' => 'height: auto; display: block; width: 100%',
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
                            'style' => 'height: auto; display: block; width: 100%',
                        ]
                    )
                                    ?>
                    <p class="exp"> <?php echo __('user_color_actual_posting_exp') ?> </p>
                </td>
            </tr>

            <tr>
                <td> <?php echo __('inline_view_on_click') ?> </td>
                <td>
                    <div class="form-group form-check">
                        <?= $this->Form->control(
                            'inline_view_on_click',
                            [
                                'type' => 'checkbox',
                                'class' => 'form-check-input',
                                'label' => [
                                    'class' => 'form-check-label',
                                    'text' => __('inline_view_on_click_exp'),
                                ],
                            ]
                        )
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td> <?php echo __('user_show_thread_collapsed') ?> </td>
                <td>
                    <div class="form-group form-check">
                        <?= $this->Form->control(
                            'user_show_thread_collapsed',
                            [
                                'type' => 'checkbox',
                                'class' => 'form-check-input',
                                'label' => [
                                    'class' => 'form-check-label',
                                    'text' => __('user_show_thread_collapsed_exp'),
                                ],
                            ]
                        )
                        ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td> <?= __('user_pers_msg') ?> </td>
                <td>
                    <div class="form-group form-check">
                        <?= $this->Form->control(
                            'personal_messages',
                            [
                                'type' => 'checkbox',
                                'class' => 'form-check-input',
                                'label' => [
                                    'class' => 'form-check-label',
                                    'text' => __('user_pers_msg_exp'),
                                ],
                            ]
                        )
?>
                    </div>
                </td>
            </tr>

            <?php if (
            !$SaitoSettings->get('category_chooser_global')
                && $SaitoSettings->get('category_chooser_user_override')
) : ?>
            <tr>
                <td>
                    <?= __('user_category_override') ?>
                </td>
                <td>
                    <div class="form-group form-check">
                        <?= $this->Form->control(
                            'user_category_override',
                            [
                                'type' => 'checkbox',
                                'class' => 'form-check-input',
                                'label' => [
                                    'class' => 'form-check-label',
                                    'text' => __('user_category_override_exp'),
                                ],
                            ]
                        )
                        ?>
                    </div>
                </td>
            </tr>
            <?php endif; ?>
        </table>


        <?php
        //= get additional profile info from plugins
        $items = $SaitoEventManager->dispatch(
            'Request.Saito.View.User.edit',
            [
                'user' => $user,
                'View' => $this,
            ]
        );
        if ($items) {
            foreach ($items as $item) {
                echo $item;
            }
        }
        ?>
    </div> <!-- card-body -->

    <div class="card-footer">
        <?= $this->Form->submit(
            __('gn.btn.save.t'),
            ['id' => 'btn-primary', 'class' => 'btn btn-primary from-control']
        ) ?>
    </div>
    <?= $this->Form->end() ?>
</div> <!-- card -->
