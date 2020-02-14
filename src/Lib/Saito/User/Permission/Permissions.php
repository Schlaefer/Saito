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
use Saito\RememberTrait;
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

    /**
     * @var \Saito\User\Permission\Roles
     */
    protected $roles;

    /**
     * @var \Saito\User\Permission\Resources
     */
    protected $resources;

    /**
     * Constructor
     *
     * @param \Saito\User\Permission\Roles $roles The roles
     * @param \Saito\User\Permission\Resources $resources The resources collection
     */
    public function __construct(Roles $roles, Resources $resources)
    {
        Stopwatch::start('Permission::__construct()');
        $this->roles = $roles;
        $this->resources = $resources;

        Stopwatch::stop('Permission::__construct()');
    }

    /**
     * Check if access to resource is allowed.
     *
     * @param string $resource Resource to check
     * @param \Saito\User\Permission\ResourceAI $identifier Identifier to provide
     * @return bool
     */
    public function check(string $resource, ResourceAI $identifier): bool
    {
        $resource = $this->resources->get($resource);

        return $resource === null ? false : $resource->check($identifier);
    }

    /**
     * Gets the roles object
     *
     * @return \Saito\User\Permission\Roles
     */
    public function getRoles(): Roles
    {
        return $this->roles;
    }

    /**
     * Build category permissions
     *
     * @param \App\Model\Table\CategoriesTable $categories Categories for accession permissions
     * @return void
     */
    public function buildCategories(CategoriesTable $categories): void
    {
        $categories = Cache::remember(
            'saito.core.permission.categories',
            function () use ($categories): array {
                $resources = [];
                $categories = $categories->getAllCategories();
                $roles = $this->roles->getAvailable(true);
                $accessions = array_combine(array_column($roles, 'id'), array_column($roles, 'type'));
                $actions = [
                    'read' => 'accession',
                    'thread' => 'accession_new_thread',
                    'answer' => 'accession_new_posting',
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

                // array [['resource' => <Resource>, 'role' => <role-type>]]
                // Resource: `saito.core.category.<category-ID>.<action>`
                return $resources;
            }
        );

        foreach ($categories as $category) {
            $this->resources->add(
                (new Resource($category['resource']))
                ->allow((new ResourceAC())
                    ->asRole($category['role']))
            );
        }
    }
}
