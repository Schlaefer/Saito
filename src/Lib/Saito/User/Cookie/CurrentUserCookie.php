<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User\Cookie;

/**
 * Handles the persistent cookie for cookie relogin
 */
class CurrentUserCookie extends Storage
{

    protected $_Cookie;

    /**
     * {@inheritDoc}
     */
    public function write($CurrentUser)
    {
        $data = ['id' => $CurrentUser->getId()];
        parent::write($data);
    }

    /**
     * Gets cookie values
     *
     * @return false|array cookie values if found, `false` otherwise
     */
    public function read()
    {
        $cookie = parent::read();

        if (!is_array($cookie)) {
            if (!is_null($cookie)) {
                // cookie couldn't be deciphered correctly and is a meaningless string
                parent::delete();
            }

            return false;
        }

        return $cookie;
    }
}
