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

use Saito\User\ForumsUserInterface;

/**
 * Resource Access Identity
 */
class ResourceAI
{
    /**
     * @var string role
     */
    protected $role = null;

    /**
     * @var int user-ID
     */
    protected $userId = null;

    /**
     * @var \Saito\User\ForumsUserInterface User to check against
     */
    protected $user = null;

    /**
     * Get the user which requests the permission
     *
     * @return \Saito\User\ForumsUserInterface|null
     */
    public function getUser(): ?ForumsUserInterface
    {
        return $this->user;
    }

    /**
     * Get owner-ID of the resource
     *
     * @return int|null
     */
    public function getOwner(): ?int
    {
        return $this->userId;
    }

    /**
     * Get owner of the resource
     *
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * Set a user which requests the permission
     *
     * @param \Saito\User\ForumsUserInterface $user The user.
     * @return self
     */
    public function asUser(ForumsUserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Set owner role
     *
     * @param string $role Owner's role
     * @return self
     */
    public function onRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Set owner identity
     *
     * @param int $userId Owner's user-ID
     * @return self
     */
    public function onOwner(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }
}
