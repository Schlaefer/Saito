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

use Saito\Event\SaitoEventManager;

/**
 * @property \App\Controller\Component\CacheSupportComponent $CacheSupport
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
            ->dispatch('saito.plugin.admin.plugins.request');
        $this->set(compact('plugins'));
    }
}
