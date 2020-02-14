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

use Saito\App\Registry;

/**
 * Resource Access Control
 */
class ResourceAC
{
    /**
     * @var array Roles as array_keys [<roleName> => true]
     */
    protected $asRole = [];

    /**
     * @var array Roles as array_keys [<roleName> => true]
     */
    protected $onRole = [];

    protected $onOwn = false;

    protected $everybody = false;

    protected $locked = false;

    /**
     * Lock the permision and disallow further changes
     *
     * @return self
     */
    public function lock(): self
    {
        $this->locked = true;

        return $this;
    }

    /**
     * Permission granted as role
     *
     * @param string $role role
     * @return self
     */
    public function asRole(string $role): self
    {
        if ($this->locked) {
            $this->handleLocked();
        }
        $this->asRole[$role] = true;

        return $this;
    }

    /**
     * Permission granted on role
     *
     * @param string $role role
     * @return self
     */
    public function onRole(string $role): self
    {
        if ($this->locked) {
            $this->handleLocked();
        }
        $this->onRole[$role] = true;

        return $this;
    }

    /**
     * Permissions granted on roles
     *
     * @param string ...$roles Roles
     * @return self
     */
    public function onRoles(...$roles): self
    {
        foreach ($roles as $role) {
            $this->onRole($role);
        }

        return $this;
    }

    /**
     * Permission granted on owner
     *
     * @return self
     */
    public function onOwn(): self
    {
        if ($this->locked) {
            $this->handleLocked();
        }
        $this->onOwn = true;

        return $this;
    }

    /**
     * Permission granted for everybody
     *
     * @return self
     */
    public function asEverybody(): self
    {
        if ($this->locked) {
            $this->handleLocked();
        }
        $this->everybody = true;

        return $this;
    }

    /**
     * Check permission against identity-provider
     *
     * @param \Saito\User\Permission\ResourceAI $identity identity
     * @return bool
     */
    public function check(ResourceAI $identity): bool
    {
        if (!empty($this->onRole)) {
            $role = $identity->getRole();
            if ($role === null || !isset($this->onRole[$role])) {
                return false;
            }
        }

        if ($this->everybody === true) {
            return true;
        }

        if ($this->onOwn === true) {
            $CU = $identity->getUser();
            $owner = $identity->getOwner();
            if ($CU !== null && $owner !== null && $CU->getId() === $owner) {
                return true;
            }
        }

        if (!empty($this->asRole)) {
            $CU = $identity->getUser();
            if ($CU !== null) {
                // @td Attach to CU
                $roles = Registry::get('Permissions')->getRoles();
                $allRoles = $roles->get($CU->getRole());
                foreach ($allRoles as $role) {
                    if (isset($this->asRole[$role])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Handle access to locked permission config
     *
     * @return void
     * @throws \RuntimeException
     */
    protected function handleLocked(): void
    {
        throw new \RuntimeException('PermissionProvider is locked.', 1573820147);
    }
}
