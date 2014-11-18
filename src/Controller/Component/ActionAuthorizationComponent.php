<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Saito\App\Registry;
use Saito\User\ForumsUserInterface;

class ActionAuthorizationComponent extends Component
{

    /**
     * Check if user is authorized to use the controller-action
     *
     * @param ForumsUserInterface $user
     * @param string $action current controller action
     * @return bool true is authorized, false otherwise
     */
    public function isAuthorized(ForumsUserInterface $user, $action)
    {
        $Controller = $this->_registry->getController();
        if (isset($Controller->actionAuthConfig[$action])) {
            $requiredRole = $Controller->actionAuthConfig[$action];

            return Registry::get('Permission')
                           ->check($user->getRole(), $requiredRole);
        }

        $isAdminRoute = $this->request->param('prefix') === 'admin';
        if ($isAdminRoute) {
            return $user->permission('saito.core.admin.backend');
        }

        return true;
    }

}
