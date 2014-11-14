<?php

	App::uses('SitemapAppController', 'Sitemap.Controller');
	App::uses('SitemapCollection', 'Sitemap.Lib');

	class SitemapsController extends SitemapAppController {

		public $uses = false;

		public $helpers = [
				'Sitemap.Sitemap'
		];

		public $generators = [
				'SitemapEntries'
		];

		protected $_Generators = null;

		public function beforeFilter() {
			$this->Auth->allow('index', 'file');
			$this->response->disableCache();
			$this->_Generators = new SitemapCollection($this->generators, $this);
		}

		public function index() {
			$this->set('files', $this->_Generators->files());
		}

		/**
		 *
		 * @param $file
		 * @throws BadRequestException
		 */
		public function file($file) {
			$this->RequestHandler->respondAs('txt');
			if (empty($file)) {
				throw new BadRequestException;
			}
			try {
				$this->set('urls', $this->_Generators->content($file));
			} catch (Exception $e) {
				throw new BadRequestException;
			}
		}

	}
