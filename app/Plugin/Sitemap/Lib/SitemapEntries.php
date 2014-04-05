<?php

	App::uses('SitemapGenerator', 'Sitemap.Lib');

	class SitemapEntries extends SitemapGenerator {

		protected $_type = 'entries';

		public function files() {
			$this->_Controller->loadModel('Entry');
			$entry = $this->_Controller->Entry->find('first',
					[
							'contain' => ['Category'],
							'conditions' => ['Category.accession' => [0, 1]],
							'order' => ['Entry.id' => 'DESC']
					]
			);

			$count = (empty($entry)) ? 0 : $entry['Entry']['id'];
			$count = intval($count / $this->_divider) + 1;
			$files = [];
			for ($i = 0; $i < $count; $i++) {
				$start = $i * $this->_divider + 1;
				$end = ($i + 1) * $this->_divider;
				$files[] = ['url' => $this->_filename([$start, $end])];
			}
			return $files;
		}

		/**
		 *
		 * @param $params
		 * @return array|mixed
		 * @throws InvalidArgumentException
		 */
		protected function _parseParams($params) {
			$start = filter_var($params[0], FILTER_VALIDATE_INT);
			$end = filter_var($params[1], FILTER_VALIDATE_INT);
			if (!$start || !$end || ($end - $start) > $this->_divider ||
					// entries must be in even divider range
					// prevents maliciously spamming the server with new cache files
					($start - 1) % $this->_divider !== 0 ||
					($end) % $this->_divider !== 0

			) {
				throw new InvalidArgumentException;
			}
			return ['start' => $start, 'end' => $end];
		}

		protected function _content($params) {
			$now = time();
			$filename = $this->_filename([$params['start'], $params['end']]);
			$cache = Cache::read($filename, 'sitemap');
			if ($cache) {
				return $cache;
			}

			$this->_Controller->loadModel('Entry');
			$entries = $this->_Controller->Entry->find('all',
					[
							'contain' => ['Category'],
							'conditions' =>
									[
											'Category.accession' => [0, 1],
											'Entry.id >=' => $params['start'],
											'Entry.id <=' => $params['end'],
									],
							'fields' => ['Entry.id', 'Entry.time', 'Entry.edited']
					]
			);
			$urls = [];
			if (empty($entries)) {
				return $urls;
			}
			foreach ($entries as $key => $entry) {
				unset($entries[$key]);
				$lastmod = strtotime($entry['Entry']['edited']);
				if ($lastmod < 1) {
					$lastmod = strtotime($entry['Entry']['time']);
				}
				if ($now > ($lastmod + (3 * DAY))) { // old entries
					$changefreq = 'monthly';
				} elseif ($now > ($lastmod + DAY)) { // recently active entries
					$changefreq = 'daily';
				} else { // currently active entries
					$changefreq = 'hourly';
				}
				$urls[] = [
						'loc' => 'entries/view/' . $entry['Entry']['id'],
						'lastmod' => Date('c', $lastmod),
						'changefreq' => $changefreq
				];
			}

			Cache::write($filename, $urls, 'sitemap');
			return $urls;
		}

	}