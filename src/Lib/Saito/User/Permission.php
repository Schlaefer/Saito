<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Saito\RememberTrait;
use Stopwatch\Lib\Stopwatch;

/**
 * Class Permission
 *
 * Implements simple access control scheme.
 *
 * @package Saito\User
 */
class Permission
{

    use RememberTrait;

    protected $groups = [
        'anon' => true,
        'user' => ['anon'],
        'mod' => ['user'],
        'admin' => ['mod']
    ];

    protected $resources = [
        'saito.core.admin.backend' => ['admin' => true],
        'saito.core.posting.edit.restricted' => ['mod' => true],
        'saito.core.posting.edit.unrestricted' => ['admin' => true],
        'saito.core.user.block' => ['admin' => true],
        'saito.core.user.delete' => ['admin' => true],
        'saito.core.user.edit' => ['admin' => true],
        'saito.core.user.view.contact' => ['admin' => true],
        'saito.core.view.ip' => ['mod' => true],
        // = controller actions =
        // @todo make action specific instead of generic group names
        'anon' => ['anon' => true],
        'user' => ['user' => true],
        'mod' => ['mod' => true],
        'admin' => ['admin' => true],
    ];

    /**
     * constructor
     */
    public function __construct()
    {
        $this->bootstrap();
    }

    /**
     * allow resource
     *
     * @param $role
     * @param $resource
     */
    public function allow($role, $resource)
    {
        $this->resources[$resource][$role] = true;
    }

    /**
     * disallow resource
     *
     * @param $role
     * @param $resource
     */
    public function disallow($role, $resource)
    {
        unset($this->resources[$resource][$role]);
    }

    /**
     * check if access to resource is allowed
     *
     * @param $role
     * @param $resource
     * @return bool
     */
    public function check($role, $resource)
    {
        Stopwatch::start('Permission::check()');
        $check = false;
        $roles = $this->getRoles($role);

        if (!empty($roles)) {
            foreach ($roles as $role) {
                if (isset($this->resources[$resource][$role])) {
                    $check = true;
                    break;
                }
            }
        }
        Stopwatch::stop('Permission::check()');

        return $check;
    }

    /**
     * resolves role and add groups
     *
     * @param $role
     * @return mixed
     */
    protected function getRoles($role)
    {
        $key = 'saito.core.permission.' . $role;

        return $this->rememberStatic($key, function () use ($role) {
            if ($this->groups[$role] === true) {
                return [$role];
            } elseif (is_array($this->groups[$role])) {
                $roles = [$role];
                foreach ($this->groups[$role] as $role) {
                    $roles = array_merge($roles, $this->getRoles($role));
                }

                return $roles;
            }

            return false;
        });
    }

    /**
     * bootstrap resources
     */
    protected function bootstrap()
    {
        Stopwatch::start('Permission::__construct()');
        $this->resources = Cache::remember('saito.core.permission.resources',
            function () {
                $this->bootstrapCategories();

                return $this->resources;
            });
        Stopwatch::stop('Permission::__construct()');
    }

    /**
     * convert category-accessions and insert them as resources
     *
     * `saito.core.category.<category-ID>.<action>`
     */
    protected function bootstrapCategories()
    {
        $Categories = TableRegistry::get('Categories');
        $categories = $Categories->getAllCategories();
        $accessions = [0 => 'anon', 1 => 'user', 2 => 'mod', 3 => 'admin'];
        $actions = [
            'read' => 'accession',
            'thread' => 'accession_new_thread',
            'answer' => 'accession_new_posting'
        ];
        foreach ($categories as $category) {
            foreach ($actions as $action => $field) {
                $role = $accessions[$category->get($field)];
                $resource = 'saito.core.category.' . $category->get('id') . '.' . $action;
                $this->allow($role, $resource);
            }
        }
    }


}
