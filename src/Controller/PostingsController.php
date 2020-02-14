<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller;

use Api\Controller\ApiAppController;
use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Saito\Exception\SaitoForbiddenException;

/**
 * Endpoint for adding/POST and editing/PUT posting
 *
 * @property \App\Model\Table\EntriesTable $Entries
 * @property \App\Controller\Component\PostingComponent $Posting
 */
class PostingsController extends ApiAppController
{
    /**
     * {@inheritDoc}
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadModel('Entries');
        $this->loadComponent('Posting');
    }

    /**
     * Add a a new posting
     *
     * @return void
     */
    public function add(): void
    {
        $data = $this->getRequest()->getData();
        $allowedFields = ['category_id', 'edited', 'edited_by', 'pid', 'subject', 'text'];
        $data = array_intersect_key($data, array_fill_keys($allowedFields, 1));

        $data += [
            'name' => $this->CurrentUser->get('username'),
            'user_id' => $this->CurrentUser->getId(),
        ];

        /** @var \App\Model\Entity\Entry $posting */
        $posting = $this->Posting->create($data, $this->CurrentUser);

        if (empty($posting)) {
            throw new BadRequestException();
        }

        $errors = $posting->getErrors();

        if (!count($errors)) {
            $this->set(compact('posting'));

            return;
        }

        $this->set(compact('errors'));
        $this->viewBuilder()->setTemplate('/Error/json/entityValidation');
    }

    /**
     * Edit an existing posting
     *
     * @param string $id Unused in favor of request-data.
     * @return void
     */
    public function edit(string $id): void
    {
        $id = $this->getRequest()->getData('id', null);

        if (empty($id)) {
            throw new BadRequestException('No posting-id provided.');
        }

        try {
            $posting = $this->Entries->get($id);
        } catch (\Throwable $e) {
            throw new NotFoundException('Posting not found.');
        }

        $data = $this->getRequest()->getData();
        $allowedFields = ['category_id', 'edited', 'edited_by', 'subject', 'text'];
        $data = array_intersect_key($data, array_fill_keys($allowedFields, 1));

        $data += [
            'edited' => bDate(),
            'edited_by' => $this->CurrentUser->get('username'),
        ];

        $updatedPosting = $this->Posting->update($posting, $data, $this->CurrentUser);

        if (!$updatedPosting) {
            throw new BadRequestException('Posting could not be saved.');
        }

        if (!$updatedPosting->hasErrors()) {
            $this->set('posting', $updatedPosting);
            $this->render('/Postings/json/add');

            return;
        }

        $errors = $updatedPosting->getErrors();
        $this->set(compact('errors'));
        $this->viewBuilder()->setTemplate('/Error/json/entityValidation');
    }

    /**
     * Serves meta information required to add or edit a posting
     *
     * @param string|null $id ID of the posting (send on edit)
     * @return void
     */
    public function meta(?string $id = null): void
    {
        $id = (int)$id;
        $isEdit = !empty($id);
        $pid = $this->getRequest()->getQuery('pid', null);
        $isAnswer = !empty($pid);

        if ($isAnswer) {
            /** @var \Saito\Posting\PostingInterface $parent */
            $parent = $this->Entries->get($pid)->toPosting()->withCurrentUser($this->CurrentUser);

            // Don't leak content of forbidden categories
            if ($parent->isAnsweringForbidden()) {
                throw new SaitoForbiddenException(
                    'Access to parent in PostingsController:meta() forbidden.',
                    ['CurrentUser' => $this->CurrentUser]
                );
            }

            $this->set('parent', $parent);
        }

        if ($isEdit) {
            /** @var \Saito\Posting\PostingInterface $posting */
            $posting = $this->Entries->get($id)->toPosting()->withCurrentUser($this->CurrentUser);
            if (!$posting->isEditingAllowed()) {
                throw new SaitoForbiddenException(
                    'Access to posting in PostingsController:meta() forbidden.',
                    ['CurrentUser' => $this->CurrentUser]
                );
            }
            $this->set('posting', $posting);
        } else {
            /// We currently don't save drafts for edits
            $where = ['user_id' => $this->CurrentUser->getId()];
            if (is_numeric($pid)) {
                $where['pid'] = $pid;
            }
            $draft = $this->Entries->Drafts->find()->where($where)->first();

            if ($draft) {
                $this->set('draft', $draft);
            }
        }

        $settings = Configure::read('Saito.Settings');

        $this->set(compact('isAnswer', 'isEdit', 'settings'));

        $action = $isAnswer ? 'answer' : 'thread';
        $categories = $this->CurrentUser->getCategories()->getAll($action, 'list');
        $this->set('categories', $categories);
    }
}
