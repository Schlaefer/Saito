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

use Saito\App\Registry;
use Saito\User\ForumsUserInterface;
use Saito\User\Permission\ResourceAI;

/**
 * Implements ForumsUserInterface
 */
trait ForumsUserTrait
{
    /**
     * {@inheritDoc}
     */
    public function getId(): int
    {
        return (int)$this->get('id');
    }

    /**
     * {@inheritDoc}
     */
    public function isUser(ForumsUserInterface $user): bool
    {
        return $user->getId() === $this->getId();
    }

    /**
     * Checks if user is forbidden.
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return (bool)$this->get('user_lock');
    }

    /**
     * Checks if user is forbidden.
     *
     * @return bool
     */
    public function isActivated(): bool
    {
        return !$this->get('activate_code');
    }

    /**
     * Get role.
     *
     * @return string
     */
    public function getRole(): string
    {
        return $this->get('user_type');
    }

    /**
     * {@inheritDoc}
     */
    public function numberOfPostings(): int
    {
        return $this->get('entry_count');
    }

    /**
     * {@inheritDoc}
     */
    public function permission(string $resource, ResourceAI $identity = null): bool
    {
        if ($identity === null) {
            $identity = new ResourceAI();
        }

        $permissions = Registry::get('Permissions');

        return $permissions->check($resource, $identity->asUser($this));
    }
}
