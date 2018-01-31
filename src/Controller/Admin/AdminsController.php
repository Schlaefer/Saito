<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Saito\Event\SaitoEventManager;

class AdminsController extends AppController
{

    public $helpers = ['Admin', 'Sitemap.Sitemap'];

    /**
     * Amdin area homepage.
     *
     * @return void
     */
    public function index()
    {
    }

    /**
     * Show PHP-info
     *
     * @return void
     */
    public function phpinfo()
    {
    }

    /**
     * Empty out all caches
     *
     * @return void
     */
    public function emptyCaches()
    {
        $this->CacheSupport->clear();
        $this->Flash->set(__('Caches cleared.'), ['element' => 'success']);
        $this->redirect($this->referer());
    }

    /**
     * List all plugins
     *
     * @return void
     */
    public function plugins()
    {
        $plugins = SaitoEventManager::getInstance()
            ->dispatch('Request.Saito.View.Admin.plugins');
        $this->set(compact('plugins'));
    }
}
