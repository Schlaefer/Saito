<?php

namespace Api\Controller;

use Api\Error\Exception\UnknownRouteException;
use Cake\Event\Event;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

class ApiCoreController extends ApiAppController
{

    /**
     * Returns basic info
     *
     * @throws NotFoundException
     * @return void
     */
    public function bootstrap()
    {
        if (!$this->request->is('GET') || !$this->request->is('json')) {
            throw new NotFoundException;
        }

        // available categories
        $this->layout = 'mobile';
        $this->Categories = TableRegistry::get('Categories');
        $categories = $this->CurrentUser->Categories->getAll('read');
        $categories = $this->Categories->find('all')
            ->select(['id', 'category_order', 'category', 'description', 'accession'])
            ->where(['id IN' => $categories]);
        $this->set('categories', $categories);
    }

    /**
     * Handles unknown routes
     *
     * @return void
     * @throws UnknownRouteException
     */
    public function unknownRoute()
    {
        throw new UnknownRouteException;
    }


    /**
     * {@inheritdoc}
     *
     * @param Event $event An Event instance
     * @return void
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->Auth->allow(['bootstrap', 'unknownRoute']);
    }
}
