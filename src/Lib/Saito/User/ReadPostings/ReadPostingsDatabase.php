<?php

namespace Saito\User\ReadPostings;

use App\Controller\Component\CurrentUserComponent;
use App\Model\Table\EntriesTable;
use App\Model\Table\UserReadsTable;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Saito\App\Registry;
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

    protected $minPostingsToKeep;

    /**
     * {@inheritDoc}
     */
    public function __construct(CurrentUserComponent $CurrentUser, UserReadsTable $storage)
    {
        parent::__construct($CurrentUser, $storage);
        $this->_registerGc();
    }

    /**
     * {@inheritDoc}
     */
    public function set($entries)
    {
        Stopwatch::start('ReadPostingsDatabase::set()');
        if (!$this->CurrentUser->isLoggedIn()) {
            return;
        }

        $entries = $this->_prepareForSave($entries);
        if (empty($entries)) {
            return;
        }

        $this->storage->setEntriesForUser($entries, $this->_getId());
        Stopwatch::stop('ReadPostingsDatabase::set()');
    }

    /**
     * {@inheritDoc}
     */
    public function delete()
    {
        $this->storage->deleteAllFromUser($this->_getId());
    }

    /**
     * calculates user quota of allowed entries in DB
     *
     * @return int
     * @throws \UnexpectedValueException
     */
    protected function _minNPostingsToKeep()
    {
        if ($this->minPostingsToKeep) {
            return $this->minPostingsToKeep;
        }
        $threadsOnPage = Configure::read('Saito.Settings.topics_per_page');
        $postingsPerThread = Configure::read('Saito.Globals.postingsPerThread');
        $pagesToCache = 1.5;
        $this->minPostingsToKeep = intval(
            $postingsPerThread * $threadsOnPage * $pagesToCache
        );
        if (empty($this->minPostingsToKeep)) {
            throw new \UnexpectedValueException();
        }

        return $this->minPostingsToKeep;
    }

    /**
     * Garbage collection
     *
     * @return void
     */
    protected function _registerGc()
    {
        $Cron = Registry::get('Cron');
        $userId = $this->_getId();
        $Cron->addCronJob("ReadUser.$userId", 'hourly', [$this, 'gcUser']);
        $Cron->addCronJob('ReadUser.global', 'hourly', [$this, 'gcGlobal']);
    }

    /**
     * removes old data from non-active users
     *
     * should prevent entries of non returning users to stay forever in DB
     *
     * @return void
     */
    public function gcGlobal()
    {
        /** @var EntriesTable */
        $Entries = $this->_getTable('Entries');
        $lastEntry = $Entries->find(
            'all',
            [
                'fields' => ['Entries.id'],
                'order' => ['Entries.id' => 'DESC']
            ]
        )->first();
        if (!$lastEntry) {
            return;
        }
        $Categories = $Entries->Categories;
        $nCategories = $Categories->find()->count();
        $entriesToKeep = $nCategories * $this->_minNPostingsToKeep();
        $lastEntryId = $lastEntry->get('id') - $entriesToKeep;
        $this->storage->deleteEntriesBefore($lastEntryId);
    }

    /**
     * removes old data from current users
     *
     * should prevent endless growing of DB if user never clicks the
     * MAR-button
     *
     * @return void
     */
    public function gcUser()
    {
        if (!$this->CurrentUser->isLoggedIn()) {
            return;
        }

        $entries = $this->_get();
        $numberOfEntries = count($entries);
        if ($numberOfEntries === 0) {
            return;
        }

        $maxEntriesToKeep = $this->_minNPostingsToKeep();
        if ($numberOfEntries <= $maxEntriesToKeep) {
            return;
        }

        $entriesToDelete = $numberOfEntries - $maxEntriesToKeep;
        // assign dummy var to prevent Strict notice on reference passing
        $dummy = array_slice($entries, $entriesToDelete, 1);
        $oldestIdToKeep = array_shift($dummy);
        $this->storage->deleteUserEntriesBefore(
            $this->_getId(),
            $oldestIdToKeep
        );

        // all entries older than (and including) the deleted entries become
        // old entries by updating the MAR-timestamp
        $Entries = $this->_getTable('Entries');
        $youngestDeletedEntry = $Entries->find(
            'all',
            [
                'conditions' => ['Entries.id' => $oldestIdToKeep],
                'fields' => ['Entries.time']
            ]
        )
            ->first();
        // can't use  $this->_CU->LastRefresh->set() because this would also
        // delete all of this user's UserRead entries
        $this->storage->Users
            ->setLastRefresh(
                $this->_getId(),
                $youngestDeletedEntry->get('time')
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function _get()
    {
        if ($this->readPostings !== null) {
            return $this->readPostings;
        }
        $this->readPostings = $this->storage->getUser($this->_getId());

        return $this->readPostings;
    }

    /**
     * Get current-user-id
     *
     * @return int
     */
    protected function _getId()
    {
        return $this->CurrentUser->getId();
    }

    /**
     * Get table
     *
     * @param string $key table
     * @return \Cake\ORM\Table
     */
    protected function _getTable($key)
    {
        return TableRegistry::get($key);
    }
}
