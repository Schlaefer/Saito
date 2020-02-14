<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller\Component;

use Cake\Controller\Component;
use Saito\User\ForumsUserInterface;

class SlidetabsComponent extends Component
{
    /**
     * S(l)idetabs used by the application
     *
     * @var array ['title' => '<cell class>']
     */
    private $_available = [
        'slidetab_recententries' => 'SlidetabRecentposts',
        'slidetab_recentposts' => 'SlidetabUserposts',
        'slidetab_userlist' => 'SlidetabUserlist',
    ];

    /**
     * Get all available slidetabs
     *
     * @return array
     */
    public function getAvailable()
    {
        return array_keys($this->_available);
    }

    /**
     * Show slidetabs
     *
     * Setup slidetab for rendering in view.
     *
     * @param string|array $slidetabs slidetabs to show
     * @return void
     */
    public function show($slidetabs = 'all')
    {
        /**
         * @var \App\Controller\AppController
         */
        $Controller = $this->getController();
        $user = $Controller->CurrentUser;
        if (!$user->isLoggedIn()) {
            $tabs = [];
        } elseif ($slidetabs === 'all') {
            $tabs = $this->_getForUser($user);
        } else {
            $tabs = $slidetabs;
        }
        $tabs = array_map(
            function ($v) {
                return $this->_available[$v];
            },
            $tabs
        );
        $Controller->set('slidetabs', $tabs);
    }

    /**
     * Get all slidetabs in correct order for user
     *
     * @param \Saito\User\ForumsUserInterface $user user
     * @return array
     */
    protected function _getForUser(ForumsUserInterface $user)
    {
        $slidetabs = $available = $this->getAvailable();

        $order = $user->get('slidetab_order');
        if (!empty($order)) {
            $slidetabsUser = unserialize($order);
            // disabled missing tabs still set in user-prefs
            $slidetabsUser = array_intersect($slidetabsUser, $available);
            // add new tabs not set in user-prefs
            $slidetabs = array_merge($slidetabsUser, $available);
            $slidetabs = array_unique($slidetabs);
        }

        return $slidetabs;
    }
}
