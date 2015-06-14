<?php

namespace Saito\Thread;

use Saito\Posting\PostingInterface;

/**
 * Class Thread collection of Postings
 */
class Thread
{

    protected $_Postings = [];

    protected $_rootId;

    protected $_unread = 0;

    /**
     * Add posting to thread
     *
     * @param PostingInterface $posting posting
     * @return void
     */
    public function add(PostingInterface $posting)
    {
        $id = $posting->get('id');
        $this->_Postings[$id] = $posting;

        if ($this->_rootId === null) {
            $this->_rootId = $id;
        } elseif ($id < $this->_rootId) {
            $this->_rootId = $id;
        }
    }

    /**
     * Get posting from thread
     *
     * @param int $id posting-ID
     * @return PostingInterface
     */
    public function get($id)
    {
        if ($id === 'root') {
            $id = $this->_rootId;
        }

        return $this->_Postings[$id];
    }

    /**
     * Get time of last answer in thread.
     *
     * @return int
     */
    public function getLastAnswer()
    {
        return strtotime($this->get('root')->get('last_answer'));
    }

    /**
     * Count postings in thread.
     *
     * @return int
     */
    public function count()
    {
        return count($this->_Postings);
    }

    /**
     * Count unread posting in thread.
     *
     * @return int
     */
    public function countUnread()
    {
        $unread = 0;
        foreach ($this->_Postings as $posting) {
            if ($posting->isUnread()) {
                $unread++;
            }
        }
        return $unread;
    }
}
