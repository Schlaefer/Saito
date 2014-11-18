<?php

	namespace Sitemap\View\Helper;

	use Cake\View\Helper;

	class SitemapHelper extends Helper {

		public $helpers = ['Url'];

		public function sitemapUrl() {
			return $this->baseUrl() . 'sitemap.xml';
		}

		public function baseUrl() {
			return $this->Url->build('/', true);
		}

	}
