<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Cron\Lib;

use Cake\Cache\Cache;

class Cron
{
    /** @var array */
    protected $jobs = [];

    /** @var int */
    protected $now;

    /** @var array|null Null if not intialized */
    private $lastRuns = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->now = time();
    }

    /**
     * Add cron job
     *
     * @param string $id unique ID for cron job
     * @param string $due due intervall
     * @param callable $func cron job
     * @return self
     */
    public function addCronJob(string $id, string $due, callable $func): self
    {
        $this->jobs[$id] = new CronJob($id, $due, $func);

        return $this;
    }

    /**
     * Run cron jobs
     *
     * @return void
     */
    public function execute()
    {
        $this->lastRuns = $this->getLastRuns();
        $jobsExecuted = false;
        foreach ($this->jobs as $job) {
            $uid = $job->getUid();
            $due = $job->getDue();
            if (!empty($this->lastRuns[$uid])) {
                if ($this->now < $this->lastRuns[$uid]) {
                    continue;
                }
            }
            $job->execute();
            $jobsExecuted = true;
            $this->lastRuns[$uid] = $due;
        }
        if ($jobsExecuted) {
            $this->saveLastRuns();
        }
    }

    /**
     * Clear history
     *
     * @return void
     */
    public function clearHistory()
    {
        $this->now = time();
        $this->lastRuns = [];
        $this->saveLastRuns();
    }

    /**
     * Get last cron runs
     *
     * @return array
     */
    protected function getLastRuns(): array
    {
        if ($this->lastRuns === null) {
            $this->lastRuns = Cache::read('Plugin.Cron.lastRuns', 'long') ?: [];
        }

        return $this->lastRuns;
    }

    /**
     * Create new cache data
     *
     * @return void
     */
    protected function saveLastRuns(): void
    {
        Cache::write('Plugin.Cron.lastRuns', $this->lastRuns, 'long');
    }
}
