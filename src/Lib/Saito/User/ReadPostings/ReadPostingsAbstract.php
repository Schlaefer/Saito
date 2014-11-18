<?php

namespace Saito\User\ReadPostings;

use App\Controller\Component\CurrentUserComponent;
use Cake\Utility\Hash;
use Saito\Posting\Posting;

/**
 * Handles read postings for the current users
 */
abstract class ReadPostingsAbstract
{

    /**
     * @var CurrentuserComponent
     */
    protected $CurrentUser;

    /**
     * @var \Saito\User\LastRefresh\LastRefreshAbstract
     */
    protected $LastRefresh;

    /**
     * array in which keys are ids of read postings
     *
     * @var array [<id-1> => 1, <id-2> => 1]
     */
    protected $readPostings = null;

    public function __construct(
        CurrentuserComponent $CurrentUser,
        $storage = null
    ) {
        $this->CurrentUser = $CurrentUser;
        $this->LastRefresh = $this->CurrentUser->LastRefresh;
    }

    /**
     * sets entry/entries as read for the current user
     *
     * @param $postings array single ['Entry' => []] or multiple [0 =>
     *     ['Entry' => …]
     */
    abstract public function set($postings);

    /**
     * checks if entry is read by the current user
     *
     * if timestamp is provided it is checked against user's last refresh
     * time
     *
     * @param int $id
     * @param mixed $timestamp unix timestamp or timestamp string
     * @return bool
     */
    public function isRead($id, $timestamp = null)
    {
        if ($timestamp !== null && $this->LastRefresh->isNewerThan($timestamp)) {
            return true;
        }

        if ($this->readPostings === null) {
            $this->get();
        }
        return isset($this->readPostings[$id]);
    }

    /**
     * delete all read entries for the current user
     */
    abstract public function delete();

    /**
     *
     * @param $postings
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function prepareForSave($postings)
    {
        // wrap single posting
        if ($postings instanceof Posting) {
            $postings = [0 => $postings];
        }

        if (empty($postings)) {
            throw new \InvalidArgumentException;
        }

        // performance: don't store entries covered by timestamp
        $postingIds = [];
        foreach ($postings as $k => $posting) {
            if (!$this->LastRefresh->isNewerThan($posting->get('time'))) {
                $postingIds[] = $posting->get('id');
            }
        }

        return $postingIds;
    }

    /**
     * gets all read postings for the current user and puts them into
     * $_readPostings
     *
     * @return array
     */
    abstract protected function get();

}
