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

interface IdentifierInterface
{
    /**
     * Constructor
     *
     * @param mixed $token The identifier token
     */
    public function __construct($token);

    /**
     * Get identifier token
     *
     * @return mixed
     */
    public function get();

    /**
     * Get type
     *
     * @return string
     */
    public function type(): string;
}
