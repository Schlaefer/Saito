<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Admin\Controller;

use App\Controller\AppController;
use Cake\Event\EventInterface;

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
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->viewBuilder()->setLayout('Admin.admin');
    }
}
