<?php

namespace Api\Controller;

use Api\Error\Exception\ApiValidationException;
use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;

class ApiEntriesController extends ApiAppController
{

    public $helpers = ['TimeH'];

    /**
     * Get all threads
     *
     * @return void
     */
    public function threadsGet()
    {
        $order = 'time';
        if ($this->request->getQuery('order') === 'answer') {
            $order = 'last_answer';
        }
        $order = [
            'Entries.fixed' => 'DESC',
            'Entries.' . $order => 'DESC'
        ];

        $limit = (int)$this->request->getQuery('limit');
        if ($limit <= 0 || $limit > 100) {
            $limit = 10;
        }

        $offset = (int)$this->request->getQuery('offset');
        if ($offset < 0) {
            $offset = 0;
        }

        $categories = $this->CurrentUser->Categories->getAll('read');
        $conditions = [
            'Entries.category_id IN' => $categories,
            'Entries.pid' => 0
        ];

        $entries = $this->Entries->find(
            'all',
            [
                'conditions' => $conditions,
                'order' => $order,
                'limit' => $limit,
                'offset' => $offset,
                'contain' => ['Categories', 'Users']
            ]
        );
        $this->set('entries', $entries);
    }

    /**
     * Create new posting
     *
     * @throws ApiValidationException
     * @throws BadRequestException
     * @return void
     */
    public function entriesItemPost()
    {
        $data = $this->request->getData();
        if (isset($data['parent_id'])) {
            $data['pid'] = $data['parent_id'];
            unset($data['parent_id']);
        }

        $new = $this->Entries->createPosting($data);
        if (($errors = $new->getErrors()) && !empty($errors)) {
            $errors = $new->getErrors();
            if (!empty($errors)) {
                $field = key($errors);
                throw new ApiValidationException(
                    $field,
                    current($errors[$field])
                );
            }
        } elseif (!$new) {
            throw new BadRequestException(
                'Entry could not be created.',
                1434352683
            );
        }

        $this->set('entry', $new);
    }

    /**
     * Get a posting
     *
     * @param string $id posting-ID
     * @return void
     * @throws NotFoundException
     * @throws BadRequestException
     */
    public function threadsItemGet($id)
    {
        if (empty($id)) {
            throw new BadRequestException('Missing entry id.');
        }

        $this->viewBuilder()->enableAutoLayout(false);

        $order = ['Entries.id' => 'ASC'];
        $categories = $this->CurrentUser->Categories->getAll('read');
        // @performace use unhydrated resultset
        $entries = $this->Entries->find(
            'all',
            [
                'conditions' => [
                    'Entries.tid' => $id,
                    'Entries.category_id IN' => $categories
                ],
                'order' => $order,
                'contain' => ['Categories', 'Users']
            ]
        )->enableHydration(false);

        if ($entries->count() === 0) {
            throw new NotFoundException(
                sprintf('Thread with id `%s` not found.', $id)
            );
        }

        $this->set('entries', $entries);
    }

    /**
     * Update a posting.
     *
     * @param null $id posting-ID
     * @return void
     * @throws NotFoundException
     * @throws BadRequestException
     * @throws ForbiddenException
     */
    public function entriesItemPut($id = null)
    {
        if (empty($id)) {
            throw new BadRequestException('Missing entry id.');
        }

        $posting = $this->Entries->get($id, ['return' => 'Entity']);
        if (empty($posting)) {
            throw new NotFoundException(
                sprintf('Entry with id `%s` not found.', $id)
            );
        }

        $isEditingForbidden = $posting->toPosting()
            ->isEditingAsCurrentUserForbidden();
        if ($isEditingForbidden === 'time') {
            throw new ForbiddenException('The editing time ran out.');
        } elseif ($isEditingForbidden === 'user') {
            throw new ForbiddenException(
                sprintf(
                    'The user `%s` is not allowed to edit.',
                    $this->CurrentUser->get('username')
                )
            );
        } elseif ($isEditingForbidden) {
            throw new ForbiddenException(
                'Editing is forbidden for unknown reason.'
            );
        }

        $data = $this->request->getData();
        $data['id'] = (int)$id;
        $posting = $this->Entries->update($posting, $data);
        if (count($posting->getErrors())) {
            throw new BadRequestException(
                'Tried to save entry but failed for unknown reason.'
            );
        }
        $this->set('entry', $posting);
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
        $this->loadModel('Entries');
        $this->Auth->allow(['threadsGet', 'threadsItemGet']);
    }
}
