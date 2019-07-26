<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Model\Entity;

use Cake\ORM\Entity;
use Saito\User\SaitoUser;

/**
 * @method string getRole()
 */
class User extends Entity
{
    /**
     * {@inheritDoc}
     *
     * Make ForumsUserInterface available.
     */
    public function __call($method, $arguments)
    {
        $suser = $this->toSaitoUser();
        if (is_callable([$suser, $method])) {
            return call_user_func_array([$suser, $method], $arguments);
        }
        $class = get_class($this);
        throw new \Exception("Invalid method {$class}::{$method}()");
    }

    /**
     * Return user as SaitoUser
     *
     * @return SaitoUser
     */
    public function toSaitoUser()
    {
        return new SaitoUser($this->toArray());
    }

    /**
     * Get number of postings
     *
     * @return mixed
     */
    public function numberOfPostings()
    {
        return $this->get('entry_count');
    }
}
