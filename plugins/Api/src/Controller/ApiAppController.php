<?php

namespace Api\Controller;

use Api\Error\Exception\ApiAuthException;
use Api\Error\Exception\ApiDisabledException;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Event\Event;

class ApiAppController extends AppController
{

    /**
     * {@inheritdoc}
     *
     * @param Event $event An Event instance
     * @return void
     */
    public function beforeFilter(Event $event)
    {
        $this->components()->unload('Security');
        parent::beforeFilter($event);

        $enabled = Configure::read('Saito.Settings.api_enabled');
        if (empty($enabled)) {
            throw new ApiDisabledException;
        }

        $allowOrigin = Configure::read('Saito.Settings.api_crossdomain');
        if (!empty($allowOrigin)) {
            $this->response->header(
                'Access-Control-Allow-Origin',
                $allowOrigin
            );
        }
    }

    /**
     * Throws Error if action is only allowed for logged in users
     *
     * @throws ApiAuthException
     * @return void
     */
    protected function _checkLoggedIn()
    {
        $this->Auth->unauthorizedRedirect = false;
        if ($this->CurrentUser->isLoggedIn() === false &&
            !in_array($this->request->action, $this->Auth->allowedActions)
        ) {
            throw new ApiAuthException();
        }
    }
}
