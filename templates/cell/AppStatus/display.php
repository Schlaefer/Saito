<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 * @var \App\View\AppView $this
 */

use Cake\Utility\Text;
?>
<p>
    <?php
    $loggedin = $Stats->getNumberOfRegisteredUsersOnline();
    if ($CurrentUser->isLoggedIn()) {
        $loggedin = $this->Html->link($loggedin, '/users/index');
    }
    echo Text::insert(
        __('discl.status'),
        [
            'entries' => number_format(
                $Stats->getNumberOfPostings(),
                null,
                null,
                '.'
            ),
            'threads' => number_format(
                $Stats->getNumberOfThreads(),
                null,
                null,
                '.'
            ),
            'registered' => number_format(
                $Stats->getNumberOfRegisteredUsers(),
                null,
                null,
                '.'
            ),
            'loggedin' => $loggedin,
            'anon' => $Stats->getNumberOfAnonUsersOnline(),
        ]
    );

    ?>
</p>
<p>
    <?php
    $user = $Stats->getLatestUser();
    $user = $this->User->linkToUserProfile($user, $CurrentUser);
    echo __('discl.newestMember', $user);
    ?>
</p>
