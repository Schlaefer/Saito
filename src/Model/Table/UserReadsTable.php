<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Stopwatch\Lib\Stopwatch;

class UserReadsTable extends Table
{

    /**
     * Caches user entries over multiple validations
     *
     * Esp. when many rows are set via Mix-view request
     *
     * @var array
     */
    protected $_userCache = null;

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
    public function setEntriesForUser($entriesId, $userId)
    {
        // filter out duplicates
        $userEntries = $this->getUser($userId);
        $entriesToSave = array_diff($entriesId, $userEntries);

        if (empty($entriesToSave)) {
            return;
        }

        $data = [];
        foreach ($entriesToSave as $entryId) {
            $this->_userCache[$userId][$entryId] = $entryId;
            $data[] = [
                'entry_id' => $entryId,
                'user_id' => $userId
            ];
        }

        $entities = $this->newEntities($data);
        // @performance is one transaction but multiple inserts
        $this->connection()->transactional(
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
    public function getUser($userId)
    {
        if (isset($this->_userCache[$userId])) {
            return $this->_userCache[$userId];
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
        $this->_userCache[$userId] = $read;
        Stopwatch::stop('UserRead::getUser()');

        return $this->_userCache[$userId];
    }

    /**
     * deletes entries with lower entry-ID than $entryId
     *
     * @param int $entryId entry-ID
     * @return void
     * @throws \InvalidArgumentException
     */
    public function deleteEntriesBefore($entryId)
    {
        if (empty($entryId)) {
            throw new \InvalidArgumentException;
        }
        $this->_userCache = null;
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
    public function deleteUserEntriesBefore($userId, $entryId)
    {
        if (empty($userId) || empty($entryId)) {
            throw new \InvalidArgumentException;
        }
        $this->_userCache = null;
        $this->deleteAll(['entry_id <' => $entryId, 'user_id' => $userId]);
    }

    /**
     * deletes entries from user $userId
     *
     * @param int $userId user-ID
     * @return void
     * @throws \InvalidArgumentException
     */
    public function deleteAllFromUser($userId)
    {
        if (empty($userId)) {
            throw new \InvalidArgumentException;
        }
        $this->_userCache = null;
        $this->deleteAll(['user_id' => $userId], false, false);
    }
}
