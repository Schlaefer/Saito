<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User;

use Saito\User\Permission\Identifier\IdentifierInterface;

interface ForumsUserInterface
{
    /**
     * Get a user setting.
     *
     * @param string $setting Setting to get.
     * @return mixed
     */
    public function get($setting);

    /**
     * Set a user setting.
     *
     * @param string $setting key
     * @param mixed $value value
     * @return void
     */
    public function set(string $setting, $value);

    /**
     * Get user's id.
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Get user's role.
     *
     * @return string
     */
    public function getRole(): string;

    /**
     * Checks if the user is forbidden.
     *
     * @return bool
     */
    public function isLocked(): bool;

    /**
     * Checks if the user is activated
     *
     * @return bool
     */
    public function isActivated(): bool;

    /**
     * Checks if the user is the same user as $user
     *
     * @param ForumsUserInterface $user - User to check against.
     * @return bool
     */
    public function isUser(ForumsUserInterface $user): bool;

    /**
     * Get number of postings
     *
     * @return int
     */
    public function numberOfPostings(): int;

    /**
     * Check if user has permission to access a resource.
     *
     * @param string $resource resource
     * @param IdentifierInterface ...$identifiers Identifier
     * @return bool
     */
    public function permission(string $resource, IdentifierInterface ...$identifiers): bool;
}
