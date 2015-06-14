<?php

namespace Saito\Posting;

use Saito\Posting\Basic\BasicPostingInterface;
use Saito\Posting\Basic\BasicPostingTrait;
use Saito\Posting\Decorator\PostingTrait;
use Saito\Posting\Decorator\UserPostingTrait;
use Saito\Thread\Thread;
use Saito\User\CurrentUser\CurrentUser;
use Saito\User\ForumsUserInterface;
use Saito\User\RemovedSaitoUser;
use Saito\User\SaitoUser;

class Posting implements BasicPostingInterface, PostingInterface
{

    use BasicPostingTrait;
    use UserPostingTrait;

    protected $_children = [];

    protected $_level;

    protected $_rawData;

    protected $_Thread;

    /**
     * Constructor.
     *
     * @param ForumsUserInterface $CurrentUser current-user
     * @param array $rawData raw posting data
     * @param array $options options
     * @param null|Thread $tree thread
     */
    public function __construct(
        ForumsUserInterface $CurrentUser,
        $rawData,
        array $options = [],
        Thread $tree = null
    ) {
        $this->_rawData = $rawData;

        if (empty($this->_rawData['user'])) {
            $this->_rawData['user'] = new RemovedSaitoUser();
        } else {
            $this->_rawData['user'] = new SaitoUser($this->_rawData['user']);
        }

        $this->setCurrentUser($CurrentUser);

        $options += ['level' => 0];
        $this->_level = $options['level'];

        if (!$tree) {
            $tree = new Thread;
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
            case (isset($this->_rawData[$var])):
                return $this->_rawData[$var];
            case (array_key_exists($var, $this->_rawData)):
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
    public function getLevel()
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
    public function map(callable $callback, $mapSelf = true, $node = null)
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
                    $this->getCurrentUser(),
                    $child,
                    ['level' => $this->_level + 1],
                    $this->_Thread
                );
            }
        }
        unset($this->_rawData['_children']);
    }
}
