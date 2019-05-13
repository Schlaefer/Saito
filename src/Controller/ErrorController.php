<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;

/**
 * Custom Error Controller
 *
 * ErrorController extends AppController so the variables for the default-theme
 * are populated and the default theme can be used to show errors.
 */
class ErrorController extends AppController
{
    /**
     * beforeRender callback
     *
     * @param \Cake\Event\Event $event Event.
     * @return void
     */
    public function beforeRender(Event $event)
    {
        parent::beforeRender($event);
        $this->viewBuilder()->setTemplatePath('Error');
    }
}
