<?php

namespace Saito\User\ReadPostings;

use App\Controller\Component\CurrentUserComponent;
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
    protected $userReadsTable;

    protected $minPostingsToKeep;

    public function __construct(
        CurrentUserComponent $CurrentUser,
        UserReadsTable $storage
    ) {
        parent::__construct($CurrentUser);
        $this->userReadsTable = $storage;
        $this->registerGc();
    }

    /**
     * @param array $entries
     */
    public function set($entries)
    {
        Stopwatch::start('ReadPostingsDatabase::set()');
        if (!$this->CurrentUser->isLoggedIn()) {
            return;
        }

        $entries = $this->prepareForSave($entries);
        if (empty($entries)) {
            return;
        }

        $this->userReadsTable->setEntriesForUser($entries, $this->getId());
        Stopwatch::stop('ReadPostingsDatabase::set()');
    }

    public function delete()
    {
        $this->userReadsTable->deleteAllFromUser($this->getId());
    }

    /**
     * calculates user quota of allowed entries in DB
     *
     * @return int
     * @throws \UnexpectedValueException
     */
    protected function minNPostingsToKeep()
    {
        if ($this->minPostingsToKeep) {
            return $this->minPostingsToKeep;
        }
        $threadsOnPage = Configure::read('Saito.Settings.topics_per_page');
        $postingsPerThread = Configure::read('Saito.Globals.postingsPerThread');
        $pagesToCache = 1.5;
        $this->minPostingsToKeep = intval($postingsPerThread * $threadsOnPage * $pagesToCache);
        if (empty($this->minPostingsToKeep)) {
            throw new \UnexpectedValueException();
        }
        return $this->minPostingsToKeep;
    }

    protected function registerGc()
    {
        $Cron = Registry::get('Cron');
        $userId = $this->getId();
        $Cron->addCronJob("ReadUser.$userId", 'hourly', [$this, 'gcUser']);
        $Cron->addCronJob('ReadUser.global', 'hourly', [$this, 'gcGlobal']);
    }

    /**
     * removes old data from non-active users
     *
     * should prevent entries of non returning users to stay forever in DB
     */
    public function gcGlobal()
    {
        $Entries = $this->getTable('Entries');
        $lastEntry = $Entries->find('all',
            [
                'fields' => ['Entries.id'],
                'order' => ['Entries.id' => 'DESC']
            ])->first();
        if (!$lastEntry) {
            return;
        }
        $Categories = $Entries->Categories;
        $nCategories = $Categories->find()->count();
        $entriesToKeep = $nCategories * $this->minNPostingsToKeep();
        $lastEntryId = $lastEntry->get('id') - $entriesToKeep;
        $this->userReadsTable->deleteEntriesBefore($lastEntryId);
    }

    /**
     * removes old data from current users
     *
     * should prevent endless growing of DB if user never clicks the
     * MAR-button
     */
    public function gcUser()
    {
        if (!$this->CurrentUser->isLoggedIn()) {
            return;
        }

        $entries = $this->get();
        $numberOfEntries = count($entries);
        if ($numberOfEntries === 0) {
            return;
        }

        $maxEntriesToKeep = $this->minNPostingsToKeep();
        if ($numberOfEntries <= $maxEntriesToKeep) {
            return;
        }

        $entriesToDelete = $numberOfEntries - $maxEntriesToKeep;
        // assign dummy var to prevent Strict notice on reference passing
        $dummy = array_slice($entries, $entriesToDelete, 1);
        $oldestIdToKeep = array_shift($dummy);
        $this->userReadsTable->deleteUserEntriesBefore(
            $this->getId(),
            $oldestIdToKeep
        );

        // all entries older than (and including) the deleted entries become
        // old entries by updating the MAR-timestamp
        $Entries = $this->getTable('Entries');
        $youngestDeletedEntry = $Entries->find(
            'all',
            [
                'conditions' => ['Entries.id' => $oldestIdToKeep],
                'fields' => ['Entries.time']
            ])
            ->first();
        // can't use  $this->_CU->LastRefresh->set() because this would also
        // delete all of this user's UserRead entries
        $this->userReadsTable->Users
            ->setLastRefresh($this->getId(), $youngestDeletedEntry->get('time'));
    }

    protected function get()
    {
        if ($this->readPostings !== null) {
            return $this->readPostings;
        }
        $this->readPostings = $this->userReadsTable->getUser($this->getId());
        return $this->readPostings;
    }

    protected function getId()
    {
        return $this->CurrentUser->getId();
    }

    protected function getTable($key) {
        return TableRegistry::get($key);
    }
}
