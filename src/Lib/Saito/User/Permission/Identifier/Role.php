<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User\Permission\Identifier;

class Role implements IdentifierInterface
{
    protected $type = 'role';

    protected $token;

    /**
     * {@inheritDoc}
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * {@inheritDoc}
     */
    public function get()
    {
        return $this->token;
    }

    /**
     * {@inheritDoc}
     */
    public function type(): string
    {
        return $this->type;
    }
}
