<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User\Blocker;

/**
 * Manually block a user through a admin/mod action
 */
class ManualBlocker extends BlockerAbstract
{

    /** @var int admin-ID */
    private $adminId;

    /** @var int duration in seconds */
    private $duration;

    /**
     * Constructor
     *
     * @param int $adminId user-ID of the person performing the block operation
     * @param int|null $duration Time in seconds how long the block should be active
     */
    public function __construct(int $adminId, ?int $duration = null)
    {
        $this->adminId = $adminId;
        $this->duration = $duration;
    }

    /**
     * {@inheritDoc}
     */
    public function getReason(): string
    {
        return '1';
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function block(int $userId): bool
    {
        $user = $this->Table->Users->get($userId);
        if (empty($user)) {
            throw new \InvalidArgumentException();
        }

        $conditions = [
            'blocked_by_user_id' => $this->adminId,
            'ended IS' => null,
            'reason' => $this->getReason(),
            'user_id' => $userId,
        ];

        if ($this->duration) {
            $conditions['ends'] = bDate(time() + $this->duration);
        }

        $entity = $this->Table->newEntity($conditions);

        return (bool)$this->Table->save($entity);
    }
}
