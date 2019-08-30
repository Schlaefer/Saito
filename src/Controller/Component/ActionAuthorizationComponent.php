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
use Saito\App\Registry;
use Saito\User\CurrentUser\CurrentUserInterface;

class ActionAuthorizationComponent extends Component
{

    /**
     * Check if user is authorized to use the controller-action
     *
     * @param CurrentUserInterface $user current-user
     * @param string $action current controller action
     * @return bool true is authorized, false otherwise
     */
    public function isAuthorized(CurrentUserInterface $user, $action)
    {
        $Controller = $this->_registry->getController();
        if (isset($Controller->actionAuthConfig)
            && isset($Controller->actionAuthConfig[$action])) {
            $requiredRole = $Controller->actionAuthConfig[$action];

            return Registry::get('Permission')
                ->check($user->getRole(), $requiredRole);
        }

        $prefix = $this->request->getParam('prefix');
        $plugin = $this->request->getParam('plugin');
        $isAdminRoute = ($prefix && strtolower($prefix) === 'admin')
            || ($plugin && strtolower($plugin) === 'admin');
        if ($isAdminRoute) {
            return $user->permission('saito.core.admin.backend');
        }

        return true;
    }
}
