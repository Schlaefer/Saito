<?php

	App::uses('ApiAppController', 'Api.Controller');

	class ApiCoreController extends ApiAppController {

		public $uses = [
			'Category'
		];

		/**
		 * Returns basic info
		 */
		public function bootstrap() {
			if ($this->request->is('GET') === false ||
					$this->request->is('json') === false
			) {
				throw new NotFoundException;
			}

			// available categories
			$this->layout = 'mobile';
			$categories = $this->Category->find('all', [
					'contain' => false,
					'conditions' => [
						'accession <=' => $this->CurrentUser->getMaxAccession()
					],
					'fields' => ['id', 'category_order', 'category', 'description', 'accession']
				]);
			$this->set('categories', Hash::extract($categories, '{n}.Category'));
		}

		public function unknownRoute() {
			throw new \Saito\Api\UnknownRouteException;
		}

		public function beforeFilter() {
			parent::beforeFilter();
			$this->Auth->allow('bootstrap', 'unknownRoute');
		}


	}