<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Posting;

use Saito\Posting\Basic\BasicPostingTrait;
use Saito\Posting\UserPosting\UserPostingTrait;
use Saito\Thread\Thread;
use Saito\User\CurrentUser\CurrentUserInterface;
use Saito\User\RemovedSaitoUser;
use Saito\User\SaitoUser;

class Posting implements PostingInterface
{
    use BasicPostingTrait;
    use UserPostingTrait;

    protected $_children = [];

    /**
     * Distance to root in tree
     *
     * @var int
     */
    protected $_level;

    protected $_rawData;

    protected $_Thread;

    /**
     * Constructor.
     *
     * @param array $rawData raw posting data
     * @param array $options options
     * @param null|\Saito\Thread\Thread $tree thread
     */
    public function __construct(
        $rawData,
        array $options = [],
        ?Thread $tree = null
    ) {
        $this->_rawData = $rawData;

        if (empty($this->_rawData['user'])) {
            $this->_rawData['user'] = new RemovedSaitoUser();
        } else {
            $this->_rawData['user'] = new SaitoUser($this->_rawData['user']);
        }

        $options += ['level' => 0];
        $this->_level = $options['level'];

        if (!$tree) {
            $tree = new Thread();
        }
        $this->_Thread = $tree;
        $this->_Thread->add($this);

        $this->_attachChildren();
    }

    /**
     * {@inheritDoc}
     */
    public function get($var)
    {
        switch (true) {
            case isset($this->_rawData[$var]):
                return $this->_rawData[$var];
            case array_key_exists($var, $this->_rawData):
                // key is set but null
                return $this->_rawData[$var];
            default:
                $message = "Attribute '$var' not found in class Posting.";
                throw new \InvalidArgumentException($message);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function withCurrentUser(CurrentUserInterface $CU): self
    {
        $this->setCurrentUser($CU);
        $this->map(function ($node) use ($CU) {
            $node->setCurrentUser($CU);
        });

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getLevel(): int
    {
        return $this->_level;
    }

    /**
     * {@inheritDoc}
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * {@inheritDoc}
     */
    public function getAllChildren()
    {
        $postings = [];
        $this->map(
            function ($node) use (&$postings) {
                $postings[$node->get('id')] = $node;
            },
            false
        );

        return $postings;
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return $this->_rawData;
    }

    /**
     * {@inheritDoc}
     */
    public function getThread()
    {
        return $this->_Thread;
    }

    /**
     * {@inheritDoc}
     */
    public function hasAnswers()
    {
        return count($this->_children) > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function map(callable $callback, bool $mapSelf = true, $node = null): void
    {
        if ($node === null) {
            $node = $this;
        }
        if ($mapSelf) {
            $callback($node);
        }
        foreach ($node->getChildren() as $child) {
            $this->map($callback, true, $child);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function addDecorator(callable $fct)
    {
        foreach ($this->_children as $key => $child) {
            $newChild = $fct($child);
            $newChild->addDecorator($fct);
            $this->_children[$key] = $newChild;
        }
        $new = $fct($this);
        // replace decorated object in Thread collection
        $this->_Thread->add($new);

        return $new;
    }

    /**
     * Attach all children recursively
     *
     * @return void
     */
    protected function _attachChildren()
    {
        if (isset($this->_rawData['_children'])) {
            foreach ($this->_rawData['_children'] as $child) {
                $this->_children[] = new Posting(
                    $child,
                    ['level' => $this->_level + 1],
                    $this->_Thread
                );
            }
        }
        unset($this->_rawData['_children']);
    }
}
