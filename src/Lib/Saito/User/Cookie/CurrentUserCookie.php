<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User\Cookie;

use Cake\Controller\Controller;
use Cake\Core\Configure;

/**
 * Handles the persistent cookie for cookie relogin
 */
class CurrentUserCookie extends Storage
{

    protected $_Cookie;

    /**
     * {@inheritDoc}
     */
    public function __construct(Controller $controller, ?string $key = null, array $config = [])
    {
        $key = Configure::read('Security.cookieAuthName');
        parent::__construct($controller, $key, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function write($id)
    {
        $data = ['id' => $id];
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
