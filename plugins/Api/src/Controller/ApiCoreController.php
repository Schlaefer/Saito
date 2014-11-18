<?php

    namespace Api\Controller;

	use Api\Controller\ApiAppController;
    use Cake\Event\Event;
    use Cake\Network\Exception\NotFoundException;
    use Cake\ORM\TableRegistry;
    use Saito\Api\UnknownRouteException;

    class ApiCoreController extends ApiAppController {

/**
 * Returns basic info
 *
 * @throws NotFoundException
 */
		public function bootstrap() {
			if (!$this->request->is('GET') || !$this->request->is('json')) {
				throw new NotFoundException;
			}

			// available categories
			$this->layout = 'mobile';
            $this->Categories = TableRegistry::get('Categories');
			$categories = $this->Categories->find('all')
                ->select(['id', 'category_order', 'category', 'description', 'accession'])
                ->where(['accession <=' => $this->CurrentUser->getMaxAccession()]);
            $this->set('categories', $categories);
		}

        /**
         * @throws UnknownRouteException
         */
        public function unknownRoute() {
            throw new UnknownRouteException;
        }

		public function beforeFilter(Event $event) {
			parent::beforeFilter($event);
			$this->Auth->allow('bootstrap', 'unknownRoute');
		}

	}
