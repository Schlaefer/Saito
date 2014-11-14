<?php

	App::uses('AppHelper', 'View/Helper');

	class SitemapHelper extends AppHelper {

		public function sitemapUrl() {
			return $this->baseUrl() . 'sitemap.xml';
		}

		public function baseUrl() {
			return $this->url('/', true);
		}

	}
