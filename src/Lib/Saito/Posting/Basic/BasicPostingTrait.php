<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Posting\Basic;

/**
 * Implements BasicPosting Interface
 */
trait BasicPostingTrait
{
    /**
     * {@inheritDoc}
     */
    abstract public function get(string $var);

    /**
     * {@inheritDoc}
     */
    public function isLocked(): bool
    {
        return (bool)$this->get('locked');
    }

    /**
     * {@inheritDoc}
     */
    public function isNt(): bool
    {
        $text = $this->get('text');

        return empty($text);
    }

    /**
     * {@inheritDoc}
     */
    public function isPinned(): bool
    {
        return (bool)$this->get('fixed');
    }

    /**
     * {@inheritDoc}
     */
    public function isRoot(): bool
    {
        return $this->get('pid') === 0;
    }
}
