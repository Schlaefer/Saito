<?php

	App::uses('ApiAppController', 'Api.Controller');

	class ApiEntriesController extends ApiAppController {

		public $uses = [
			'Entry'
		];

		public $helpers = [
			'Api.Api'
		];

		public function threadsGet() {
			$order = 'time';
			if (isset($this->request->query['order']) && $this->request->query['order'] === 'answer') {
				$order = 'last_answer';
			}
			$order = [
				'Entry.fixed'     => 'DESC',
				'Entry.' . $order => 'DESC'
			];

			$limit = 10;
			if (isset($this->request->query['limit'])) {
				$limitQuery = (int)$this->request->query['limit'];
				if ($limitQuery > 0 && $limitQuery < 100) {
					$limit = $limitQuery;
				}
			}

			$offset = 0;
			if (isset($this->request->query['offset'])) {
				$offset = (int)$this->request->query['offset'];
			}

			$conditions = [
				'Entry.category' => $this->Entry->Category->getCategoriesForAccession(
					$this->CurrentUser->getMaxAccession()
				),
				'Entry.pid'      => 0
			];

			$entries = $this->Entry->find(
				'all',
				[
					'conditions' => $conditions,
					'order'      => $order,
					'limit'      => $limit,
					'offset'     => $offset,
					'contain'    => ['Category', 'User']
				]
			);
			$this->set('entries', $entries);
		}

		public function entriesItemPost() {
			if ($this->request->is('json') === false ||
					$this->request->is('post') === false
			) {
				throw new MethodNotAllowedException;
			}

			$data['Entry'] = $this->request->data;
			if (isset($data['Entry']['category_id'])) {
				$data['Entry']['category'] = $data['Entry']['category_id'];
				unset($data['Entry']['category_id']);
			}
			if (isset($data['Entry']['parent_id'])) {
				$data['Entry']['pid'] = $data['Entry']['parent_id'];
				unset($data['Entry']['parent_id']);
			}

			$new_posting = $this->Entry->createPosting($data);
			// @todo error/validation handling
			if ($new_posting) {
				$this->set(
					'entry',
					$this->Entry->get($new_posting['Entry']['id'], true)
				);
			} else {
				$this->response->statusCode(400);
				$this->set('error', [
						'message' => 'Entry could no be created.',
					]);
			}
		}

		public function threadsItemGet($id) {

			if (empty($id)) {
				throw new BadRequestException('Missing entry id.');
			}

			$this->autoLayout = false;

			$order = 'Entry.id asc';
			$entries = $this->Entry->find(
				'all',
				[
					'conditions' => [
						'Entry.tid'      => $id,
						// @todo test cases
						'Entry.category' => $this->Entry->Category->getCategoriesForAccession(
							$this->CurrentUser->getMaxAccession()
						),
					],
					'order'      => $order,
					'contain'    => ['Category', 'User']
				]
			);

			if (!$entries) {
				throw new NotFoundException(sprintf('Thread with id `%s` not found.', $id));
			}

			$this->set('entries', $entries);
		}

		public function entriesItemPut($id = null) {

			if (empty($id)) {
				throw new BadRequestException('Missing entry id.');
			}

			$oldEntry = $this->Entry->get($id);
			if (!$oldEntry) {
				throw new NotFoundException(sprintf('Entry with id `%s` not found.', $id));
			}

			$isEditingForbidden = $oldEntry['rights']['isEditingForbidden'];
			if ($isEditingForbidden === 'time') {
				throw new ForbiddenException('The editing time ran out.');
			} elseif ($isEditingForbidden === 'user') {
				throw new ForbiddenException(sprintf(
					'The user `%s` is not allowed to edit.',
					$this->CurrentUser['username']
				));
			} elseif ($isEditingForbidden == true) {
				throw new ForbiddenException('Editing is forbidden for unknown reason.');
			}

			$data['Entry'] = $this->request->data;
			$data['Entry']['id'] = $id;
			$entry = $this->Entry->update($data);
			if ($entry) {
				$this->initBbcode();
				$this->set(
					'entry',
					$this->Entry->get($entry['Entry']['id'], true)
				);
			} else {
				throw new BadRequestException('Tried to save entry but failed for unknown reason.');
			}
		}

		public function beforeFilter() {
			parent::beforeFilter();
			// @todo performance; load only when needed for output of BBCode html
			$this->_loadSmilies();
			$this->Auth->allow('threadsGet', 'threadsItemGet');
		}

	}
