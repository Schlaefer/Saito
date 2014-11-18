<?php

	namespace Sitemap\Controller;

	use App\Controller\AppController;
	use Cake\Event\Event;
	use Cake\Network\Exception\BadRequestException;
	use Sitemap\Lib\SitemapCollection;

	class SitemapsController extends AppController {

		public $uses = false;

		public $helpers = [
				'Sitemap.Sitemap'
		];

		public $generators = [
				'SitemapEntries'
		];

		protected $_Generators = null;

		public function beforeFilter(Event $event) {
			$this->Auth->allow(['index', 'file', 'foo']);
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
			if (empty($file)) {
				throw new BadRequestException;
			}
			try {
				$this->set('urls', $this->_Generators->content($file));
			} catch (\Exception $e) {
				throw new BadRequestException;
			}
		}

	}
