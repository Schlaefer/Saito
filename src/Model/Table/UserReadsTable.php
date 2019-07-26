<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Model\Table;

use App\Model\Table\UsersTable;
use Cake\ORM\Table;
use Stopwatch\Lib\Stopwatch;

/**
 * @property UsersTable $Users
 */
class UserReadsTable extends Table
{

    /**
     * Caches user entries over multiple validations
     *
     * Esp. when many rows are set via Mix-view request
     *
     * @var array
     */
    protected $userCache = [];

    /**
     * {@inheritDoc}
     */
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', ['foreignKey' => 'user_id']);
    }

    /**
     * sets $entriesIds as read for user $userId
     *
     * @param array $entriesId [3, 4, 34]
     * @param int $userId user-ID
     * @return void
     */
    public function setEntriesForUser(array $entriesId, int $userId): void
    {
        // filter out duplicates
        $userEntries = $this->getUser($userId);
        $entriesToSave = array_diff($entriesId, $userEntries);

        if (empty($entriesToSave)) {
            return;
        }

        $data = [];
        foreach ($entriesToSave as $entryId) {
            $this->userCache[$userId][$entryId] = $entryId;
            $data[] = [
                'entry_id' => $entryId,
                'user_id' => $userId
            ];
        }

        $entities = $this->newEntities($data);
        // @performance is one transaction but multiple inserts
        $this->getConnection()->transactional(
            function () use ($entities) {
                foreach ($entities as $entity) {
                    $this->save($entity, ['atomic' => false]);
                }
            }
        );
    }

    /**
     * gets all read postings of user with id $userId
     *
     * @param int $userId user-ID
     * @return array [1 => 1, 3 => 3]
     */
    public function getUser(int $userId): array
    {
        if (isset($this->userCache[$userId])) {
            return $this->userCache[$userId];
        }

        Stopwatch::start('UserRead::getUser()');
        $readPostings = $this->find()
            ->where(['user_id' => $userId])
            ->order('entry_id');
        $read = [];
        foreach ($readPostings as $posting) {
            $id = $posting->get('entry_id');
            $read[$id] = $id;
        }
        $this->userCache[$userId] = $read;
        Stopwatch::stop('UserRead::getUser()');

        return $this->userCache[$userId];
    }

    /**
     * deletes entries with lower entry-ID than $entryId
     *
     * @param int $entryId entry-ID
     * @return void
     * @throws \InvalidArgumentException
     */
    public function deleteEntriesBefore(int $entryId): void
    {
        if (empty($entryId)) {
            throw new \InvalidArgumentException;
        }
        $this->userCache = [];
        $this->deleteAll(['entry_id <' => $entryId]);
    }

    /**
     * deletes entries with lower entry-ID than $entryId from user $userId
     *
     * @param int $userId user-ID
     * @param int $entryId entry-ID
     * @return void
     * @throws \InvalidArgumentException
     */
    public function deleteUserEntriesBefore(int $userId, int $entryId): void
    {
        if (empty($userId) || empty($entryId)) {
            throw new \InvalidArgumentException;
        }
        unset($this->userCache[$userId]);
        $this->deleteAll(['entry_id <' => $entryId, 'user_id' => $userId]);
    }

    /**
     * deletes entries from user $userId
     *
     * @param int $userId user-ID
     * @return void
     * @throws \InvalidArgumentException
     */
    public function deleteAllFromUser(int $userId): void
    {
        if (empty($userId)) {
            throw new \InvalidArgumentException;
        }
        unset($this->userCache[$userId]);
        $this->deleteAll(['user_id' => $userId]);
    }
}
