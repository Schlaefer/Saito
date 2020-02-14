<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User\Userlist;

use Cake\ORM\TableRegistry;
use Saito\RememberTrait;

/**
 * Lazy load list of all users and cache
 */
class UserlistModel
{
    use RememberTrait;

    /**
     * Returns array with list of usernames
     *
     * @return array usernames
     */
    public function get(): array
    {
        return $this->remember('userlist', function () {
            /** @var \App\Model\Table\UsersTable $users */
            $users = TableRegistry::get('Users');

            return $users->userlist();
        });
    }
}
