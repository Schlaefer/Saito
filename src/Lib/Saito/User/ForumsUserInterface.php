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

use App\Model\Entity\User;

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
     * Sets all (and replaces existing) settings for a user
     *
     * @param array $settings Settings
     * @return void
     */
    public function setSettings(array $settings): void;

    /**
     * Get all settings
     *
     * @return array
     */
    public function getSettings(): array;

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
     * Check if user has permission to access a resource.
     *
     * @param string $resource resource
     * @return bool
     */
    public function permission(string $resource): bool;
}
