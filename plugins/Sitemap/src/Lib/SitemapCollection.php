<?php

	namespace Sitemap\Lib;

	use Cake\Cache\Cache;
	use Cake\Controller\Controller;

	class SitemapCollection {

		protected $_Generators = [];

		public function __construct(array $generators, Controller $Controller) {
			if (!Cache::config('sitemap')) {
				Cache::config(
					'sitemap',
					[
						'prefix' => 'saito_',
						'engine' => 'File',
						'groups' => ['sitemap'],
						'path' => CACHE
					]
				);
			}
			foreach ($generators as $name) {
				$this->_add($name, $Controller);
			}
		}

		protected function _add($name, Controller $Controller) {
			$name = 'Sitemap\\Lib\\' . $name;
			$this->_Generators[$name] = new $name($Controller);
		}

		public function files() {
			$files = [];
			foreach ($this->_Generators as $Generator) {
				$files += $Generator->files();
			}
			return $files;
		}

		public function content($file) {
			$contents = [];
			foreach ($this->_Generators as $Generator) {
				$content = $Generator->content($file);
				if ($content) {
					$contents = array_merge($contents, $content);
				}
			}
			return $contents;
		}

	}