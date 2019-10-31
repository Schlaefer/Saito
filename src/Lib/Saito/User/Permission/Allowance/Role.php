<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User\Permission\Allowance;

class Role
{
    protected $subjects = [];
    protected $objects = [];
    protected $resource;

    /**
     * Constructor
     *
     * @param string $resource What is granted permission to
     * @param string|array $subjects Who is granted permission
     * @param string|array $objects Qualifier for permission resource
     */
    public function __construct($resource, $subjects, $objects = null)
    {
        $subjects = is_array($subjects) ? $subjects : [$subjects];
        $objects = $objects ?: [];
        $objects = is_array($objects) ? $objects : [$objects];

        $this->subjects = array_fill_keys($subjects, true);
        $this->objects = array_fill_keys($objects, true);
        $this->resource = $resource;
    }

    /**
     * Check if allowed
     *
     * @param string $resource Resource-ID
     * @param string|array $roles Subject
     * @param string $object Object
     * @return bool
     */
    public function check(string $resource, $roles, string $object = null): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        if ($this->resource !== $resource) {
            return false;
        }

        $isRole = false;
        foreach ($roles as $role) {
            if (isset($this->subjects[$role])) {
                $isRole = true;
                break;
            }
        }
        if (!$isRole) {
            return false;
        }

        if (!empty($this->objects)) {
            if (empty($object)) {
                return false;
            }

            if (!isset($this->objects[$object])) {
                return false;
            }
        }

        return true;
    }
}
