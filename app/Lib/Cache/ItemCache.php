<?php

	App::uses('Stopwatch', 'Stopwatch.Lib');

	class ItemCache {

		protected $_cache = null;

		/**
		 * @var null|SaitoCacheEngine if null cache is only active for this request
		 */
		protected $_CacheEngine = null;

		protected $_settings = [
			'duration' => null,
			'maxItems' => null,
			// +/- percentage maxItems can deviate before gc is triggered
			'maxItemsFuzzy' => 0.05
		];

		protected $_name;

		protected $_now;

		protected $_updated = false;

		public function __construct($name, SaitoCacheEngineInterface $CacheEngine = null, $options = []) {
			$this->_settings = $options + $this->_settings;
			$this->_now = time();
			$this->_name = $name;
			$this->_CacheEngine = $CacheEngine;
		}

		public function __destruct() {
			if ($this->_cache === null) {
				return;
			}
			$this->_write();
		}

		public function delete($key) {
			if ($this->_cache === null) {
				$this->_read();
			}
			$this->_updated = true;
			unset($this->_cache[$key]);
		}

		public function get($key = null) {
			if ($this->_cache === null) {
				$this->_read();
			}
			if ($key === null) {
				return $this->_cache;
			}
			if (!isset($this->_cache[$key])) {
				return null;
			}
			return $this->_cache[$key]['content'];
		}

		/**
		 *
		 *
		 * @param $key
		 * @param $timestamp
		 * @param callable $comp
		 * @return mixed
		 * @throws InvalidArgumentException
		 */
		public function compareUpdated($key, $timestamp, callable $comp) {
			if (!isset($this->_cache[$key])) {
				throw new InvalidArgumentException;
			}
			return $comp($this->_cache[$key]['metadata']['content_last_updated'],
				$timestamp);
		}

		public function set($key, $content, $timestamp = null) {
			if ($this->_cache === null) {
				$this->_read();
			}

			$this->_updated = true;

			if (!$timestamp) {
				$timestamp = $this->_now;
			}
			$metadata = [
				'created' => $this->_now,
				'content_last_updated' => $timestamp,
			];

			$data = ['metadata' => $metadata, 'content' => $content];
			$this->_cache[$key] = $data;
		}

		protected function _read() {
			if ($this->_CacheEngine === null) {
				$this->_cache = [];
				return;
			}
			Stopwatch::start("ItemCache read: {$this->_name}");
			$this->_cache = $this->_CacheEngine->read($this->_name);
			if (empty($this->_cache)) {
				$this->_cache = [];
			}
			if ($this->_settings['duration']) {
				$this->_gcOutdated();
			}
			Stopwatch::stop("ItemCache read: {$this->_name}");
			return;
		}

		public function reset() {
			$this->_updated = true;
			$this->_cache = [];
		}

		protected function _gcOutdated() {
			Stopwatch::start("ItemCache _gcOutdated: {$this->_name}");
			$expired = time() - $this->_settings['duration'];
			foreach ($this->_cache as $key => $item) {
				if ($item['metadata']['created'] < $expired) {
					unset($this->_cache[$key]);
					$this->_updated = true;
				}
			}
			Stopwatch::stop("ItemCache _gcOutdated: {$this->_name}");
		}

		/**
		 * garbage collection max items
		 *
		 * costly function for larger arrays, relieved by maxItemsFuzzy
		 */
		protected function _gcMaxItems() {
			// Stopwatch::start("ItemCache _gxMaxItems: {$this->_name}");

			$fuzzy = $this->_settings['maxItemsFuzzy'];
			$max = (int)($this->_settings['maxItems'] * (1 + $fuzzy));

			if (count($this->_cache) <= $max) {
				Stopwatch::stop("ItemCache _gxMaxItems: {$this->_name}");
				return;
			}

			// keep items which were last used/updated
			uasort($this->_cache, function ($a, $b) {
				if ($a['metadata']['content_last_updated'] === $b['metadata']['content_last_updated']) {
					return 0;
				}
				return ($a['metadata']['content_last_updated'] < $b['metadata']['content_last_updated']) ? 1 : -1;
			});

			$min = (int)($this->_settings['maxItems'] * (1 - $fuzzy));
			$this->_cache = array_slice($this->_cache, 0, $min, true);
			// Stopwatch::stop("ItemCache _gxMaxItems: {$this->_name}");
		}

		protected function _write() {
			if (empty($this->_cache) || $this->_CacheEngine === null || !$this->_updated) {
				return;
			}
			if ($this->_settings['maxItems']) {
				$this->_gcMaxItems();
			}
			$this->_CacheEngine->write($this->_name, $this->_cache);
		}

	}