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

use Saito\User\Permission\Allowance\Force;
use Saito\User\Permission\Allowance\Owner;
use Saito\User\Permission\Allowance\Role;

class PermissionConfig
{
    protected $boolAllowances = [];

    protected $roleAllowances = [];

    protected $ownerAllowances = [];

    /**
     * Allows or forbids without any further check (use with care)
     *
     * @param string $resource Resource to allow
     * @param bool $allowed Force true or false
     * @return self
     */
    public function allowAll(string $resource, bool $allowed = true): self
    {
        $this->boolAllowances[$resource] = new Force($resource, $allowed);

        return $this;
    }

    /**
     * Allow the owner of the resource to access resource
     *
     * @param string $resource Resource to allow
     * @return self
     */
    public function allowOwner(string $resource): self
    {
        $this->ownerAllowances[$resource][] = new Owner($resource);

        return $this;
    }

    /**
     * Allow role for resource
     *
     * @param string $resource Resource to allow
     * @param array|string $role role
     * @param array|string|null $object object
     * @return self
     */
    public function allowRole(string $resource, $role, $object = null): self
    {
        $this->roleAllowances[$resource][] = new Role($resource, $role, $object);

        return $this;
    }

    /**
     * Get owner config
     *
     * @param string $resource Resource
     * @return array
     */
    public function getOwner(string $resource): array
    {
        return $this->ownerAllowances[$resource] ?? [];
    }

    /**
     * Get roles config
     *
     * @param string $resource Resource
     * @return array
     */
    public function getRole(string $resource): array
    {
        return $this->roleAllowances[$resource] ?? [];
    }

    /**
     * Get forced config
     *
     * @param string $resource Resource
     * @return Force|null
     */
    public function getForce(string $resource): ?Force
    {
        return $this->boolAllowances[$resource] ?? null;
    }
}
