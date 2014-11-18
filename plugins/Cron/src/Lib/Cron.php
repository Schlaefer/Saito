<?php

	namespace Cron\Lib;

	use Cake\Cache\Cache;
	use Cake\Core\Configure;
    use Cake\Log\Log;

    class Cron {

		protected $_jobs = [];

		protected $_lastRuns = null;

		protected $_now;

		/**
		 * defines time intervals in seconds
		 *
		 * @var array
		 */
		protected $_dues = [
			// a little shorter than a Cake's default cache-config invalidation hour
			'hourly' => 3300,
			// if no cron job was triggered in one hour then Cake's default cache file is
			// invalidated and hourly is also triggered
			'daily' => 86400
		];

		public function __construct() {
			$this->_now = time();
		}

		public function addCronJob($id, $due, callable $func) {
			$this->_jobs[$id] = new CronJob($id, $due, $func);
		}

		public function execute() {
			$lastRuns = $this->_getLastRuns();
			$jobsExecuted = 0;
			foreach ($this->_jobs as $job) {
				if (!empty($lastRuns[$job->due][$job->uid])) {
					if (isset($this->_dues[$job->due])) {
						$due = $lastRuns[$job->due][$job->uid] + $this->_dues[$job->due];
					} else {
						$due = strtotime($job->due, $lastRuns[$job->due][$job->uid]);
					}
					if ($this->_now < $due) {
						continue;
					}
				}
				$jobsExecuted++;
				$job->execute();
				$this->_log('Run cron-job ' . $job->uid);
				$this->_lastRuns[$job->due][$job->uid] = $this->_now;
			}
			if ($jobsExecuted === 0) {
				return;
			}
			Cache::write('Plugin.Cron.lastRuns', $this->_lastRuns);
		}

		public function clearHistory() {
			$this->_lastRuns = [];
			$this->_now = time();
			Cache::write('Plugin.Cron.lastRuns', $this->_getNewCacheData());
		}

		protected function _getNewCacheData() {
			return ['meta' => ['lastDailyReset' => $this->_now]];
		}

		protected function _getLastRuns() {
			if ($this->_lastRuns) {
				return $this->_lastRuns;
			}
			$cache = Cache::read('Plugin.Cron.lastRuns');

			if ( // cache file is not created yet
					!isset($cache['meta']['lastDailyReset']) ||
					// cache is outdated
					$cache['meta']['lastDailyReset'] + $this->_dues['daily'] < $this->_now
			) {
				$cache = $this->_getNewCacheData();
				// This request may trigger many jobs and take some time.
				// Update cache immediately and not after all jobs are done (at the
				// end of this request), so that following requests arriving in that
				// time-frame don't assume they have to run the same jobs too.
				Cache::write('Plugin.Cron.lastRuns', $cache);
			}
			$this->_lastRuns = $cache;
			return $this->_lastRuns;
		}

		protected function _log($msg) {
			if (Configure::read('Saito.Globals.logInfo')) {
				return Log::write('info', $msg, ['scope' => ['saito.info']]);
			}
		}

	}
