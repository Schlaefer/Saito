<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Saito\User\ForumsUserInterface;

class SlidetabsComponent extends Component
{
    /**
     * S(l)idetabs used by the application
     *
     * @var array
     */
    private $_available = [
        'slidetab_recentposts',
        'slidetab_recententries',
        'slidetab_userlist',
        'slidetab_shoutbox'
    ];

    /**
     * Get all available slidetabs
     *
     * @return array
     */
    public function getAvailable()
    {
        return $this->_available;
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
        $Controller = $this->_registry->getController();
        $user = $Controller->CurrentUser;
        if (!$user->isLoggedIn()) {
            $tabs = false;
        } elseif ($slidetabs === 'all') {
            $tabs = $this->_getForUser($user);
        } else {
            $tabs = $slidetabs;
        }
        $Controller->set('slidetabs', $tabs);
    }

    /**
     * Get all slidetabs in correct order for user
     *
     * @param ForumsUserInterface $user user
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

        if (!Configure::read('Saito.Settings.shoutbox_enabled')) {
            unset($slidetabs[array_search('slidetab_shoutbox', $slidetabs)]);
        }

        return $slidetabs;
    }
}
