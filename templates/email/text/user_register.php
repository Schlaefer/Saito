<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 * @var \App\View\AppView $this
 */
echo __(
    'register_email_content',
    [
        $forumName,
        $this->Url->build(
            [
                'controller' => 'users',
                'action' => 'rs',
                $user->get('id'),
                '?' => ['c' => $user->get('activate_code')],
            ],
            ['fullBase' => true]
        ),
    ]
);
echo $this->element('email/text/footer');
