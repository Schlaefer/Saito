<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller\Component;

use Cake\Controller\Component;
use Saito\User\CurrentUser\CurrentUserInterface;

/**
 * Class AutoRefreshComponent
 *
 * @package App\Controller\Component
 */
class AutoReloadComponent extends Component
{
    /**
     * Set auto refresh time
     *
     * @param CurrentUserComponent|int $period period in minutes
     *
     * @return void
     */
    public function after($period)
    {
        if ($period instanceof CurrentUserInterface) {
            $CurrentUser = $period;
            if (!$CurrentUser->isLoggedIn()) {
                return;
            }
            $period = $CurrentUser->get('user_forum_refresh_time');
        }
        if (!is_numeric($period) || $period <= 0) {
            return;
        }
        $period = $period * 60;
        $this->_registry->getController()->set('autoPageReload', $period);
    }
}
