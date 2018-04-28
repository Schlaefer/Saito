<?php

namespace Admin\Controller;

use App\Controller\AppController;
use Cake\Event\Event;

class AdminAppController extends AppController
{
    public $helpers = [
        'Admin.Admin',
        // Bootstrap-UI-plugin helpers overwrite default Cake helpers
        'Html' => ['className' => 'BootstrapUI.Html'],
        'Form' => ['className' => 'BootstrapUI.Form'],
        'Flash' => ['className' => 'BootstrapUI.Flash'],
        'Paginator' => ['className' => 'BootstrapUI.Paginator'],
        'Breadcrumbs' => ['className' => 'BootstrapUI.Breadcrumbs'],
    ];

    /**
     * {@inheritDoc}
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->viewBuilder()->layout('Admin.admin');
    }
}
