<?php

namespace Saito\Posting;

use Saito\Posting\Basic\BasicPostingInterface;
use Saito\Thread\Thread;

/**
 * Interface PostingInterface
 *
 * @package Saito\Posting
 */
interface PostingInterface extends BasicPostingInterface
{
    /**
     * Get sub-postings one level below this posting (direct answers)
     *
     * @return array of postings
     */
    public function getChildren();

    /**
     * Get all sub-postings on all level below this postings
     *
     * @return array of postings
     */
    public function getAllChildren();

    /**
     * Get level of posting in thread
     *
     * @return mixed
     */
    public function getLevel();

    /**
     * Get thread for posting.
     *
     * @return Thread
     */
    public function getThread();

    /**
     * Check if posting has answers.
     *
     * @return bool
     */
    public function hasAnswers();

    /**
     * Map posting and all children to callback
     *
     * @param callable $callback callback
     * @param bool $mapSelf map this posting
     * @param PostingInterface $node root posting for callbacks to apply
     * @return void
     */
    public function map(callable $callback, $mapSelf = true, $node = null);

    /**
     * Get raw posting data
     *
     * @td @sm @perf Benchmark and remove if O.K.
     *
     * @return array
     */
    public function toArray();

    /**
     * Attach decorators to posting.
     *
     * @param callable $fct callback
     * @return PostingInterface new posting
     */
    public function addDecorator(callable $fct);
}
