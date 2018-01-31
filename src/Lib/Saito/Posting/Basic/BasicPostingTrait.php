<?php

namespace Saito\Posting\Basic;

/**
 * Implements BasicPosting Interface
 */
trait BasicPostingTrait
{
    /**
     * {@inheritDoc}
     */
    public function isLocked()
    {
        return (bool)$this->get('locked');
    }

    /**
     * {@inheritDoc}
     */
    public function isNt()
    {
        $text = $this->get('text');

        return empty($text);
    }

    /**
     * {@inheritDoc}
     */
    public function isPinned()
    {
        return (bool)$this->get('fixed');
    }

    /**
     * {@inheritDoc}
     */
    public function isRoot()
    {
        return $this->get('pid') === 0;
    }
}
