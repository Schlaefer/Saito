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

/**
 * Force allowance
 */
class Force
{
    /** @var string $resource */
    protected $resource;

    /** @var bool $allowed */
    protected $allowed;

    /**
     * Constructor
     *
     * @param string $resource What is granted permission to
     * @param bool $allowed True or false
     */
    public function __construct(string $resource, bool $allowed = true)
    {
        $this->resource = $resource;
        $this->allowed = $allowed;
    }

    /**
     * Check if allowed i.e. user matches.
     *
     * @param string $resource Resource to check
     * @return bool
     */
    public function check(string $resource): bool
    {
        if ($this->resource !== $resource) {
            return false;
        }

        return $this->allowed;
    }
}
