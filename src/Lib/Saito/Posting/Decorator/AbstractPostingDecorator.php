<?php

namespace Saito\Posting\Decorator;

use Saito\Posting;
use Saito\Posting\Basic\BasicPostingInterface;
use Saito\Posting\PostingInterface;

/**
 * Abstract class which allows to decorate Postings
 *
 * class MyDecorator extends AbstractPostingDecorator ...
 *
 * $postings = $postings->addDecorator(
 *   function ($node) {
 *      return new MyDecorator($node);
 *    }
 * );
 */
abstract class AbstractPostingDecorator implements BasicPostingInterface, PostingInterface
{

    protected $_Posting;

    /**
     * Make traits and other decorators available
     *
     * @param string $method method to call
     * @param array $args method arguments
     * @return mixed return value of called method
     */
    public function __call($method, $args)
    {
        if (is_callable([$this->_Posting, $method])) {
            return call_user_func_array([$this->_Posting, $method], $args);
        }
        throw new \RuntimeException(
            'Undefined method ' . get_class($this) . '::' . $method
        );
    }

    /**
     * {@inheritDoc}
     */
    public function __construct(\Saito\Posting\Posting $Posting)
    {
        $this->_Posting = $Posting;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function get($var)
    {
        return $this->_Posting->get($var);
    }

    /**
     * {@inheritDoc}
     */
    public function getChildren()
    {
        return $this->_Posting->getChildren();
    }

    /**
     * {@inheritDoc}
     */
    public function getLevel()
    {
        return $this->_Posting->getLevel();
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {
        return $this->_Posting->toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function getThread()
    {
        return $this->_Posting->getThread();
    }

    /**
     * {@inheritDoc}
     */
    public function hasAnswers()
    {
        return $this->_Posting->hasAnswers();
    }

    /**
     * {@inheritDoc}
     */
    public function isNt()
    {
        return $this->_Posting->isNt();
    }

    /**
     * Check if posting is locked.
     *
     * @return bool
     */
    public function isLocked()
    {
        return $this->_Posting->isLocked();
    }

    /**
     * {@inheritDoc}
     */
    public function isPinned()
    {
        return $this->_Posting->isPinned();
    }

    /**
     * {@inheritDoc}
     */
    public function isRoot()
    {
        return $this->_Posting->isRoot();
    }

    /**
     * {@inheritDoc}
     */
    public function addDecorator(callable $fct)
    {
        return $this->_Posting->addDecorator($fct);
    }

    /**
     * {@inheritDoc}
     */
    public function map(callable $callback, $mapSelf = true, $node = null)
    {
        return $this->_Posting->map($callback, $mapSelf, $node);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllChildren()
    {
        return $this->_Posting->getAllChildren();
    }
}
