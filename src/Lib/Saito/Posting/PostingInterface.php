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

use Saito\Posting\Basic\BasicPostingInterface;
use Saito\Posting\UserPosting\UserPostingInterface;
use Saito\Thread\Thread;

/**
 * Posting properties derived from the other postings building a (sub)thread
 */
interface PostingInterface extends BasicPostingInterface, UserPostingInterface
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
     * @return int
     */
    public function getLevel(): int;

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
    public function map(callable $callback, bool $mapSelf = true, PostingInterface $node = null): void;

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
