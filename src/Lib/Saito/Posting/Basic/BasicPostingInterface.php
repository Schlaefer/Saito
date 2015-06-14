<?php

namespace Saito\Posting\Basic;

interface BasicPostingInterface
{
    /**
     * Get posting property
     *
     * @param string $var kehy
     * @return mixed
     */
    public function get($var);

    /**
     * Check if posting is locked.
     *
     * @return bool
     */
    public function isLocked();

    /**
     * Check if posting is subject only.
     *
     * @return bool
     */
    public function isNt();

    /**
     * Check if posting is pinned
     *
     * @return bool
     */
    public function isPinned();

    /**
     * Check if posting is thread-root.
     *
     * @return bool
     */
    public function isRoot();
}
