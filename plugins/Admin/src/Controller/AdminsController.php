<?php

namespace Admin\Controller;

use App\Controller\Component\CacheSupportComponent;
use Saito\Event\SaitoEventManager;

/**
 * @property CacheSupportComponent $CacheSupport
 */
class AdminsController extends AdminAppController
{

    public $helpers = ['Sitemap.Sitemap'];

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
    public function emptyCaches(): void
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
