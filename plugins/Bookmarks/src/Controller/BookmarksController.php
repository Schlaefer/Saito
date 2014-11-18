<?php

	namespace Bookmarks\Controller;

	use App\Controller\AppController;
	use Cake\Event\Event;
	use Cake\Network\Exception\BadRequestException;
	use Cake\Network\Exception\MethodNotAllowedException;
	use Cake\Network\Exception\NotFoundException;
	use Cake\ORM\TableRegistry;
	use Saito\App\Registry;

	class BookmarksController extends AppController {

	/**
	 * @throws MethodNotAllowedException
	 */
		public function index() {
			if (!$this->CurrentUser->isLoggedIn()) {
				throw new MethodNotAllowedException;
			}
			$this->loadModel('Bookmarks.Bookmarks');
			$bookmarks = $this->Bookmarks->find('all', [
				'contain' => ['Entries' => ['Categories', 'Users']],
				'conditions' => ['Bookmarks.user_id' => $this->CurrentUser->getId()],
				'order' => 'Bookmarks.id DESC',
			]);
			$this->set('bookmarks', $bookmarks);
		}

	/**
	 * @return bool
	 * @throws MethodNotAllowedException
	 * @throws BadRequestException
	 */
		public function add() {
			if (!$this->request->is('ajax')) {
				throw new BadRequestException;
			}
			if (!$this->CurrentUser->isLoggedIn()) {
				throw new MethodNotAllowedException;
			}
			$this->autoRender = false;
			if (!$this->request->is('post')) {
				return false;
			}

			$data = [
				'user_id' => $this->CurrentUser->getId(),
				'entry_id' => $this->request->data['id'],
			];
			$this->Bookmark->create();
			return (bool)$this->Bookmark->save($data);
		}

		/**
		 * @param null $id
		 * @throws MethodNotAllowedException
		 */
		public function edit($id = null) {
			$bookmark = $this->_getBookmark($id);

			if (!$this->request->is('post') && !$this->request->is('put')) {
				$posting = $bookmark->get('entry');
				$posting = Registry::newInstance(
					'\Saito\Posting\Posting',
					['rawData' => $posting->toArray()]
				);
				$this->set(compact('bookmark', 'posting'));
				return;
			}

			$bookmark->set('id', $id);
			$this->Bookmarks->patchEntity(
				$bookmark,
				$this->request->data(),
				['fieldList' => ['comment']]
			);

			$success = $this->Bookmarks->save($bookmark);
			if (!$success) {
				$this->Flash->set(
					__('The bookmark could not be saved. Please, try again.')
				);
				return;
			}
			$this->redirect(['action' => 'index', '#' => $bookmark->get('entry_id')]);
		}

	/**
	 * @param null $id
	 * @return bool
	 * @throws BadRequestException
	 */
		public function delete($id = null) {
			if (!$this->request->is('ajax')) {
				throw new BadRequestException;
			}
			$this->autoRender = false;

			$bookmark = $this->_getBookmark($id);
			$success = (bool)$this->Bookmarks->delete($bookmark);
			$this->response->body($success);
			$this->response->type('json');
			return $this->response;
		}

		public function beforeFilter(Event $event) {
			parent::beforeFilter($event);
			$this->Security->config('unlockedActions', ['add']);
		}

		/**
		 * @param $id
		 * @throws NotFoundException
		 * @throws MethodNotAllowedException
		 * @throws Saito\Exception\SaitoForbiddenException
		 * @return mixed
		 */
		protected function _getBookmark($id) {
			if (!$this->CurrentUser->isLoggedIn()) {
				throw new MethodNotAllowedException;
			}

			if (!$this->Bookmarks->exists($id)) {
				throw new NotFoundException(__('Invalid bookmark.'));
			}

			$bookmark = $this->Bookmarks->find()
				->contain(['Entries' => ['Categories', 'Users']])
				->where(['Bookmarks.id' => $id])
				->first();

			if ($bookmark->get('user_id') !== $this->CurrentUser->getId()) {
				throw new Saito\Exception\SaitoForbiddenException("Attempt to edit bookmark $id.");
			}
			return $bookmark;
		}

	}