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

use Saito\Posting\Posting;
use Saito\User\CurrentUser\CurrentUserInterface;
use Saito\User\ReadPostings\ReadPostingsInterface;

/**
 * Handles read postings for the current users
 */
abstract class ReadPostingsAbstract implements ReadPostingsInterface
{

    /**
     * @var CurrentUserInterface
     */
    protected $CurrentUser;

    /**
     * array in which keys are ids of read postings
     *
     * @var array [<id-1> => 1, <id-2> => 1]
     */
    protected $readPostings = null;

    /** @var mixed $storage storage for read postings */
    protected $storage;

    /**
     * @var \Saito\User\LastRefresh\LastRefreshInterface
     */
    protected $LastRefresh;

    /**
     * Constructor.
     *
     * @param CurrentUserInterface $CurrentUser current-user
     * @param mixed $storage $storage
     */
    public function __construct(
        CurrentUserInterface $CurrentUser,
        $storage = null
    ) {
        $this->CurrentUser = $CurrentUser;
        $this->LastRefresh = $this->CurrentUser->getLastRefresh();
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function set($postings);

    /**
     * {@inheritdoc}
     */
    public function isRead($id, $timestamp = null)
    {
        if (
            ($timestamp !== null)
            && $this->LastRefresh->isNewerThan($timestamp)
        ) {
            return true;
        }

        if ($this->readPostings === null) {
            $this->_get();
        }

        return isset($this->readPostings[$id]);
    }

    /**
     * {@inheritdoc}
     */
    abstract public function delete();

    /**
     * Prepare postings for save.
     *
     * @param Posting|array $postings - Postings which are read
     * @return array posting-IDs
     */
    protected function _prepareForSave($postings)
    {
        // wrap single posting
        if ($postings instanceof Posting) {
            $postings = [0 => $postings];
        }

        if (empty($postings)) {
            throw new \InvalidArgumentException();
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
    abstract protected function _get();
}
