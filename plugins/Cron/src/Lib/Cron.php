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

    /** @var bool Should garbage collection be run before persisting */
    protected $runGc = false;

    /** @var int Now */
    protected $now;

    /** @var array|null Null if not intialized */
    protected $lastRuns = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->now = time();
        $this->addCronJob('Cron.Cron.enableGc', '+1 day', [$this, 'enableGc']);
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
        $this->loadLastRuns();
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
        if (!$jobsExecuted) {
            return;
        }
        $this->saveLastRuns();
    }

    /**
     * Enables Gc for outdated cron jobs.
     *
     * @return void
     */
    public function enableGc(): void
    {
        $this->runGc = true;
    }

    /**
     * Garbage collection on last-runs data
     *
     * Jobs that were due but not executed are removed. If the job doesn't exist
     * anymore it was GCed. If the job just wasn't registered it will be
     * executed without last run date nontheless next time it is registered.
     *
     * @return void
     */
    protected function garbageCollection(): void
    {
        foreach ($this->lastRuns as $key => $lastRun) {
            if ($this->now >= $lastRun) {
                unset($this->lastRuns[$key]);
            }
        }
    }

    /**
     * Get last cron runs
     *
     * @return void
     */
    protected function loadLastRuns(): void
    {
        $this->lastRuns = Cache::read('Plugin.Cron.lastRuns', 'long') ?: [];
    }

    /**
     * Create new cache data
     *
     * @return void
     */
    protected function saveLastRuns(): void
    {
        if ($this->runGc) {
            $this->garbageCollection();
        }
        Cache::write('Plugin.Cron.lastRuns', $this->lastRuns, 'long');
    }
}
