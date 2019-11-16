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
use Saito\User\Permission\Resource;
use Saito\User\Permission\ResourceAC;
use Saito\User\Permission\Resources;
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

    /** @var Resources */
    protected $resources;

    /** @var CategoriesTable */
    protected $categories;

    /**
     * Constructor
     *
     * @param Roles $roles The roles
     * @param Resources $resources The resources collection
     * @param CategoriesTable $categories Categories for accession permissions
     */
    public function __construct(Roles $roles, Resources $resources, CategoriesTable $categories)
    {
        Stopwatch::start('Permission::__construct()');
        $this->roles = $roles;
        $this->resources = $resources;
        $this->categories = $categories;

        $categories = Cache::remember(
            'saito.core.permission.categories',
            function () {
                return $this->bootstrapCategories();
            }
        );
        foreach ($categories as $category) {
            $this->resources->add(
                (new Resource($category['resource']))
                ->allow((new ResourceAC())
                    ->asRole($category['role']))
            );
        }

        Stopwatch::stop('Permission::__construct()');
    }

    /**
     * Check if access to resource is allowed.
     *
     * @param string $resource Resource to check
     * @param ResourceAI $identifier Identifier to provide
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
