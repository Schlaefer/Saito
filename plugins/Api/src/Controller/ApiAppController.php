<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Api\Controller;

use App\Controller\AppController;
use Cake\Controller\Component\AuthComponent;
use Cake\Core\Configure;
use Cake\Event\Event;

/**
 * Api App Controller
 *
 * @property CurrentUserComponent $CurrentUser
 */
class ApiAppController extends AppController
{
    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        // Initialize Jwt-auth before parent, so its config is before other CurrentUser Auth-conf
        $this->initializeJwtAuth($this->loadComponent('Auth'));

        parent::initialize();

        if ($this->components()->has('Csrf')) {
            $this->components()->unload('Csrf');
        }
        if ($this->components()->has('Security')) {
            $this->components()->unload('Security');
        }
    }

    /**
     * Initialize Jwt-Auth
     *
     * @see https://github.com/ADmad/cakephp-jwt-auth
     * @param AuthComponent $auth Cake's auth-component
     * @return void
     */
    private function initializeJwtAuth(AuthComponent $auth): void
    {
        $auth->setConfig([
            'storage' => 'Memory',
            'authenticate' => [
                'ADmad/JwtAuth.Jwt' => [
                    'userModel' => 'Users',
                    'key' => Configure::read('Security.cookieSalt'),
                    'fields' => [
                        'username' => 'id'
                    ],

                    'parameter' => 'token',

                    // Boolean indicating whether the "sub" claim of JWT payload
                    // should be used to query the Users model and get user info.
                    // If set to `false` JWT's payload is directly returned.
                    'queryDatasource' => true,
                ]
            ],

            'unauthorizedRedirect' => false,
            'checkAuthIn' => 'Controller.initialize',

            // If you don't have a login action in your application set
            // 'loginAction' to false to prevent getting a MissingRouteException.
            'loginAction' => false
        ]);
    }
}
