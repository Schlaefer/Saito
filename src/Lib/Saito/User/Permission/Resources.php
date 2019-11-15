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

use Saito\User\Permission\Resource;

class Resources
{
    /** @var \Saito\User\Permission\Resource[] Resources */
    protected $resources = [];

    /**
     * Add resource
     *
     * @param \Saito\User\Permission\Resource $resource Resource to add
     * @return self
     */
    public function add(Resource $resource): self
    {
        $this->resources[$resource->getName()] = $resource;

        return $this;
    }

    /**
     * Get resource
     *
     * @param string $resouce Name of resource to get
     * @return \Saito\User\Permission\Resource|null Resource or null of resource not found
     */
    public function get(string $resouce): ?Resource
    {
        return $this->resources[$resouce] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function __clone()
    {
        foreach ($this->resources as $key => $resource) {
            $this->resources[$key] = clone $resource;
        }
    }
}
