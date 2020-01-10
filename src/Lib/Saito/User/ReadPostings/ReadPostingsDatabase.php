<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User\ReadPostings;

use App\Model\Table\EntriesTable;
use App\Model\Table\UserReadsTable;
use Cake\Core\Configure;
use Saito\App\Registry;
use Saito\User\CurrentUser\CurrentUserInterface;
use Stopwatch\Lib\Stopwatch;

/**
 * Handles read postings by a server table. Used for logged-in users.
 */
class ReadPostingsDatabase extends ReadPostingsAbstract
{
    /**
     * @var UserReadsTable
     */
    protected $storage;

    /**
     * @var EntriesTable
     */
    protected $entriesTable;

    /**
     * {@inheritDoc}
     */
    public function __construct(
        CurrentUserInterface $CurrentUser,
        UserReadsTable $storage,
        EntriesTable $entriesTable
    ) {
        $this->entriesTable = $entriesTable;

        parent::__construct($CurrentUser, $storage);

        $Cron = Registry::get('Cron');
        $userId = $this->getUserId();
        $Cron->addCronJob("ReadUserDb.$userId", '+12 hours', [$this, 'garbageCollection']);
    }

    /**
     * {@inheritDoc}
     */
    public function set($entries)
    {
        Stopwatch::start('ReadPostingsDatabase::set()');
        $entries = $this->_prepareForSave($entries);
        if (empty($entries)) {
            return;
        }
        $this->storage->setEntriesForUser($entries, $this->getUserId());
        Stopwatch::stop('ReadPostingsDatabase::set()');
    }

    /**
     * {@inheritDoc}
     */
    public function delete()
    {
        $this->storage->deleteAllFromUser($this->getUserId());
    }

    /**
     * Calculates the max number of postings to remember (limits DB storage).
     *
     * @return int
     * @throws \UnexpectedValueException
     */
    protected function postingsPerUser(): int
    {
        $threadsOnPage = Configure::read('Saito.Settings.topics_per_page');
        $postingsPerThread = Configure::read('Saito.Globals.postingsPerThread');
        $pagesToCache = 1.5;
        $minPostingsToKeep = intval($postingsPerThread * $threadsOnPage * $pagesToCache);
        if (empty($minPostingsToKeep)) {
            throw new \UnexpectedValueException();
        }

        return $minPostingsToKeep;
    }

    /**
     * Removes old read-posting data from a single user.
     *
     * Prevent growing of DB if user never clicks the MAR-button.
     *
     * @return void
     */
    public function garbageCollection()
    {
        $readPostings = $this->_get();
        $numberOfRp = count($readPostings);
        if ($numberOfRp === 0) {
            return;
        }

        $maxRpToKeep = $this->postingsPerUser();
        $numberOfRpToDelete = $numberOfRp - $maxRpToKeep;
        if ($numberOfRpToDelete <= 0) {
            // Number under GC threshold or user has no data at all.
            return;
        }

        // assign dummy var to prevent Strict notice on reference passing
        $dummy = array_slice($readPostings, $numberOfRpToDelete, 1);
        $idOfOldestPostingToKeepInRp = array_shift($dummy);

        /// Update last refresh
        // All entries older than (and including) the deleted entries become
        // old entries by updating the MAR-timestamp.
        $oldestPostingToKeepInRp = $this->entriesTable->find()
            ->where(['id' => $idOfOldestPostingToKeepInRp])
            ->first();

        if (empty($oldestPostingToKeepInRp)) {
            // Posting was deleted for whatever reason: Skip this gc run. Next
            // time a later posting will be the oldest one to keep.
            return;
        }

        // Can't use  $this->_CU->LastRefresh->set(): that would not only delete
        // old but *all* of the user's individually read postings.
        $this->storage->Users
            ->setLastRefresh(
                $this->getUserId(),
                $oldestPostingToKeepInRp->get('time')
            );

        /// Now delete the old entries
        $this->storage->deleteUserEntriesBefore($this->getUserId(), $idOfOldestPostingToKeepInRp);
    }

    /**
     * {@inheritDoc}
     */
    protected function _get()
    {
        if ($this->readPostings !== null) {
            return $this->readPostings;
        }
        $this->readPostings = $this->storage->getUser($this->getUserId());

        return $this->readPostings;
    }

    /**
     * Get current-user-id
     *
     * @return int
     */
    protected function getUserId()
    {
        return $this->CurrentUser->getId();
    }
}
