<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\View\Helper;

use Cake\View\Helper\HtmlHelper;
use Cake\View\Helper\UrlHelper;
use Saito\App\Registry;
use Saito\User\Permission\Permissions;

/**
 * Class UserHelper
 *
 * @package App\View\Helper
 * @property HtmlHelper $Html
 * @property UrlHelper $Url
 */
class PermissionsHelper extends AppHelper
{
    /**
     * Permissions
     *
     * @var Permissions
     */
    private $Permissions;

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        /** @var Permissions */
        $permissions = Registry::get('Permissions');
        $this->Permissions = $permissions;
    }

    /**
     * Get an array to use as $option in a select field with role-IDs
     *
     * @param bool $includeAnon Include anon-user
     * @return array
     */
    public function rolesSelectId(bool $includeAnon = false): array
    {
        $roles = $this->Permissions->getRoles()->getAvailable($includeAnon);

        return array_map(function ($role) {
            return ['value' => $role['id'], 'text' => $this->roleAsString($role['id'])];
        }, $roles);
    }

    /**
     * Get an array to use as $option in a select field with role-types
     *
     * @param bool $includeAnon Include anon-user
     * @return array
     */
    public function rolesSelectType(bool $includeAnon = false): array
    {
        $roles = $this->Permissions->getRoles()->getAvailable($includeAnon);

        return array_map(function ($role) {
            return ['value' => $role['type'], 'text' => $this->roleAsString($role['id'])];
        }, $roles);
    }

    /**
     * L10n a role.
     *
     * @param int|string $id Role-ID or role-type.
     * @return string The localized string.
     */
    public function roleAsString($id): string
    {
        if (is_string($id)) {
            $id = $this->Permissions->getRoles()->typeToId($id);
        }

        return __d('nondynamic', 'permission.role.' . $id);
    }
}
