<?php

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Event\Event;

/**
 * Tools Controller
 *
 * @property Tool $Tool
 */
class ToolsController extends AppController
{

    /**
     * Web frontend for Javascript test-cases
     *
     * @return void
     */
    public function testJs()
    {
        $this->viewBuilder()->enableAutoLayout(false);
    }

    /**
     * {@inheritdoc}
     *
     * @param Event $event An Event instance
     * @return void
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        if (!Configure::read('debug')) {
            echo 'Please activate debug mode.';
            exit;
        }
        $this->Auth->allow('testJs');
    }
}
