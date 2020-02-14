<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

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
     * @param \Saito\Posting\PostingInterface $posting posting
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
     * @param int|string $id posting-ID
     * - <int> - Posting with that id
     * - 'root' - Root-posting
     * @return \Saito\Posting\PostingInterface
     */
    public function get($id): PostingInterface
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
    public function getLastAnswer(): int
    {
        /** @var \DateTime $lastAnswer */
        $lastAnswer = $this->get('root')->get('last_answer');

        return $lastAnswer->getTimestamp();
    }

    /**
     * Count postings in thread.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->_Postings);
    }

    /**
     * Count unread posting in thread.
     *
     * @return int
     */
    public function countUnread(): int
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
