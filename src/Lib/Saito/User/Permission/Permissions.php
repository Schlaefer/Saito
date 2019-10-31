<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User\Permission;

use App\Model\Table\CategoriesTable;
use Cake\Cache\Cache;
use InvalidArgumentException;
use Saito\RememberTrait;
use Saito\User\ForumsUserInterface;
use Saito\User\Permission\Identifier\IdentifierInterface;
use Saito\User\Permission\Roles;
use Stopwatch\Lib\Stopwatch;

/**
 * Class Permission
 *
 * Implements simple access control scheme.
 *
 * @package Saito\User
 */
class Permissions
{
    use RememberTrait;

    /** @var Roles */
    protected $roles;

    /** @var PermissionConfig */
    protected $PermissionConfig;

    /** @var CategoriesTable */
    protected $categories;

    /**
     * Constructor
     *
     * @param Roles $roles The roles
     * @param PermissionConfig $permissionConfig The config
     * @param CategoriesTable $categories Categories for accession permissions
     */
    public function __construct(Roles $roles, PermissionConfig $permissionConfig, CategoriesTable $categories)
    {
        Stopwatch::start('Permission::__construct()');
        $this->roles = $roles;
        $this->PermissionConfig = $permissionConfig;
        $this->categories = $categories;

        $categories = Cache::remember(
            'saito.core.permission.categories',
            function () {
                return $this->bootstrapCategories();
            }
        );
        foreach ($categories as $resource) {
            $this->PermissionConfig->allowRole($resource['resource'], $resource['role']);
        }

        Stopwatch::stop('Permission::__construct()');
    }

    /**
     * Check if access to resource is allowed.
     *
     * @param ForumsUserInterface $user CurrentUser
     * @param string $resource Resource
     * @param IdentifierInterface ...$identifiers Identifiers
     * @return bool
     */
    public function check(ForumsUserInterface $user, string $resource, IdentifierInterface ...$identifiers): bool
    {
        /// Force allow all check
        $force = $this->PermissionConfig->getForce($resource);
        if ($force) {
            return $force->check($resource);
        }

        $roleObject = null;

        if (!empty($identifiers)) {
            foreach ($identifiers as $identifier) {
                $type = $identifier->type();
                switch ($type) {
                    case ('owner'):
                        /// Owner check
                        foreach ($this->PermissionConfig->getOwner($resource) as $allowance) {
                            if ($allowance->check($resource, $user, $identifier->get())) {
                                return true;
                            }
                        }
                        break;
                    case ('role'):
                        // Just remember if there's a role object. Performed below.
                        $roleObject = $identifier->get();
                        break;
                    default:
                        new InvalidArgumentException(
                            sprintf('Unknown identifier type "%s" in permissin check.', $type)
                        );
                }
            }
        }

        /// Role check
        $roleAllowances = $this->PermissionConfig->getRole($resource);
        if (!empty($roleAllowances)) {
            foreach ($roleAllowances as $allowance) {
                $role = $user->getRole();
                $roles = $this->roles->get($role);
                if ($allowance->check($resource, $roles, $roleObject)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Gets the roles object
     *
     * @return Roles
     */
    public function getRoles(): Roles
    {
        return $this->roles;
    }

    /**
     * convert category-accessions and insert them as resources
     *
     * Resource: `saito.core.category.<category-ID>.<action>`
     *
     * @return array [['resource' => <Resource>, 'role' => <role-type>]]
     */
    protected function bootstrapCategories(): array
    {
        $resources = [];
        $categories = $this->categories->getAllCategories();
        $roles = $this->roles->getAvailable(true);
        $accessions = array_combine(array_column($roles, 'id'), array_column($roles, 'type'));
        $actions = [
            'read' => 'accession',
            'thread' => 'accession_new_thread',
            'answer' => 'accession_new_posting'
        ];
        foreach ($categories as $category) {
            foreach ($actions as $action => $field) {
                if (empty($accessions[$category->get($field)])) {
                    continue;
                }
                $role = $accessions[$category->get($field)];
                $categoryId = $category->get('id');
                $resource = "saito.core.category.{$categoryId}.{$action}";
                $resources[] = ['resource' => $resource, 'role' => $role];
            }
        }

        return $resources;
    }
}
