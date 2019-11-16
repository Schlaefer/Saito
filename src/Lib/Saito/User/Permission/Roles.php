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

use RuntimeException;

class Roles
{
    /**
     * Old mylittleforum string based user roles
     *
     * @var array
     */
    private $roles = [];

    /**
     * Shadows $this->roles for easy access with integer ID
     *
     * @var array
     */
    private $rolesAsInt = [];

    /**
     * Adds a new role
     *
     * @param string $role Short title like 'user', 'mod', or 'admin'
     * @param int $id A unique id for this role
     * @param array $subroles Other roles this role represents
     * @return self
     */
    public function add(string $role, int $id, array $subroles = []): self
    {
        $this->roles[$role] = ['id' => $id, 'type' => $role];
        $this->roles[$role]['subroles'] = $subroles;
        $this->rolesAsInt[$id] = $this->roles[$role];

        return $this;
    }

    /**
     * Get all roles for role
     *
     * @param string $role Role
     * @param bool $includeAnon Include 'anon' user in roles-list
     * @param bool $includeOwn If false a list all other roles a user has
     * @return array All roles a role has
     */
    public function get(string $role, bool $includeAnon = true, bool $includeOwn = true): array
    {
        if (!isset($this->roles[$role])) {
            return [];
        }

        $roles = [];

        if ($includeOwn) {
            $roles[] = $role;
        }

        foreach ($this->roles[$role]['subroles'] as $role) {
            if ($role === 'anon' && !$includeAnon) {
                continue;
            }
            $roles[] = $role;
        }

        return $roles;
    }

    /**
     * Get all configured  roles
     *
     * @param bool $includeAnon Include anon user
     * @return array
     */
    public function getAvailable(bool $includeAnon = false): array
    {
        $roles = $this->rolesAsInt;
        if (!$includeAnon) {
            unset($roles[0]);
        }

        return $roles;
    }

    /**
     * Get role id for type
     *
     * @param string $type Type
     * @return int
     */
    public function typeToId(string $type): int
    {
        if (isset($this->roles[$type])) {
            return $this->roles[$type]['id'];
        }

        throw new RuntimeException(sprintf('Role "%s" not found.', $type));
    }
}
