<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Auth;

use Cake\Auth\BaseAuthenticate;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Saito\User\Cookie\CurrentUserCookie;
use Saito\User\Cookie\Storage;

class CookieAuthenticate extends BaseAuthenticate
{
    /**
     * Manages the persistent login cookie
     *
     * @var \Saito\User\Cookie\CurrentUserCookie
     */
    private $PersistentCookie = null;

    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return ['Auth.logout' => 'logout'];
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(ServerRequest $request, Response $response)
    {
        $cookie = $this->getCookie()->read();
        if (empty($cookie)) {
            return false;
        }

        // identification field for value store in cookie is user.id not user.username
        $this->setConfig('fields', ['username' => 'id']);

        $user = $this->_findUser($cookie['id']);
        if ($user) {
            return $user;
        }

        return false;
    }

    /**
     * Handles logout: i.e. delete cookie
     *
     * @param Event $event the event
     * @param array $user the user
     * @return void
     */
    public function logout(Event $event, array $user)
    {
        $this->getCookie()->delete();
    }

    /**
     * Creates the cookie storage
     *
     * @return Storage the cookie
     */
    private function getCookie(): Storage
    {
        if (empty($this->PersistentCookie)) {
            $this->PersistentCookie = new CurrentUserCookie($this->_registry->getController());
        }

        return $this->PersistentCookie;
    }
}
