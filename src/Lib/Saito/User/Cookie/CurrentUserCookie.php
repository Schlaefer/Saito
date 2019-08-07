<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User\Cookie;

use Cake\Chronos\Chronos;
use Cake\Controller\Controller;
use Cake\Core\Configure;

/**
 * Handles the persistent cookie for cookie relogin
 */
class CurrentUserCookie extends Storage
{
    /**
     * {@inheritDoc}
     */
    public function __construct(Controller $controller, ?string $key = null, array $config = [])
    {
        $key = $key ?: Configure::read('Security.cookieAuthName');
        $config += ['expire' => '+30 days', 'refreshAfter' => '+23 days'];
        parent::__construct($controller, $key, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function write($id): void
    {
        $refreshAfter = Chronos::parse($this->getConfig('refreshAfter'));
        $data = ['id' => $id, 'refreshAfter' => $refreshAfter->getTimestamp()];
        parent::write($data);
    }

    /**
     * Gets cookie values
     *
     * @return null|array cookie values if found, null otherwise
     */
    public function read(): ?array
    {
        $cookie = parent::read();

        if (!is_array($cookie) || empty($cookie['id'])) {
            if (!is_null($cookie)) {
                // cookie couldn't be deciphered correctly and is a meaningless string
                parent::delete();
            }

            return null;
        }

        $this->refresh($cookie);
        unset($cookie['refreshAfter']);

        return $cookie;
    }

    /**
     * Refreshs the cookie so that regularly visiting users aren't logged-out
     *
     * Cookie is valid for 30 days and is renewed if used for loggin-in within 7
     * days before expiring.
     *
     * @param array $cookie cookie-data
     * @return void
     */
    private function refresh(array $cookie): void
    {
        if (empty($cookie['refreshAfter'])) {
            /// previous forum version with the cookie missing this field
            $cookie['refreshAfter'] = 0;
        }

        if ((int)$cookie['refreshAfter'] > time()) {
            return;
        }

        $this->write($cookie['id']);
    }
}
