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

class Resource
{
    /** @var string resource name */
    protected $name;

    /** @var ResourceAC[] Allowed permissions */
    protected $allowed = [];

    /** @var ResourceAC[] Disallowed permissions */
    protected $disallowed = [];

    /**
     * Constructor
     *
     * @param string $name resource name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Get resource name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Allow the resource on a permission
     *
     * @param ResourceAC $permission permission
     * @return self
     */
    public function allow(ResourceAC $permission): self
    {
        $permission->lock();
        $this->allowed[] = $permission;

        return $this;
    }

    /**
     * Disallow the resource on a permission
     *
     * @param ResourceAC $permission permission
     * @return self
     */
    public function disallow(ResourceAC $permission): self
    {
        $permission->lock();
        $this->disallowed[] = $permission;

        return $this;
    }

    /**
     * Check resource against identity
     *
     * @param ResourceAI $identity Identity
     * @return bool
     */
    public function check(ResourceAI $identity): bool
    {
        foreach ($this->disallowed as $permission) {
            if ($permission->check($identity)) {
                return false;
            }
        }

        foreach ($this->allowed as $permission) {
            if ($permission->check($identity)) {
                return true;
            }
        }

        return false;
    }
}
