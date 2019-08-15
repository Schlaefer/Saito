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
use App\Model\Entity\Entry;
use App\Model\Table\EntriesTable;
use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Saito\Exception\SaitoForbiddenException;
use Saito\Posting\PostingInterface;

/**
 * Endpoint for adding/POST and editing/PUT posting
 *
 * @property EntriesTable $Entries
 */
class PostingsController extends ApiAppController
{
    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadModel('Entries');
    }

    /**
     * Add a a new posting
     *
     * @return void
     */
    public function add(): void
    {
        $data = $this->request->getData();

        /** @var Entry */
        $posting = $this->Entries->createPosting($data, $this->CurrentUser);

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
     * @return void
     */
    public function edit(): void
    {
        $data = $this->request->getData();

        if (empty($data['id'])) {
            throw new BadRequestException('No posting-id provided.');
        }

        $id = $data['id'];
        $posting = $this->Entries->get($id, ['return' => 'Entity']);
        if (!$posting) {
            throw new NotFoundException('Posting not found.');
        }

        $updatedPosting = $this->Entries->updatePosting($posting, $data, $this->CurrentUser);

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
     * @return void
     */
    public function meta(): void
    {
        $id = $this->getRequest()->getQuery('id', null);
        $isEdit = !empty($id);
        $pid = $this->getRequest()->getQuery('pid', null);
        $isAnswer = !empty($pid);

        if ($isAnswer) {
            /** @var PostingInterface */
            $parent = $this->Entries->get($pid);

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
            /** @var PostingInterface */
            $posting = $this->Entries->get($id);
            if (!$posting->isEditingAllowed()) {
                throw new SaitoForbiddenException(
                    'Access to posting in PostingsController:meta() forbidden.',
                    ['CurrentUser' => $this->CurrentUser]
                );
            }
            $this->set('posting', $posting);
        }

        $settings = Configure::read('Saito.Settings');

        $this->set(compact('isAnswer', 'isEdit', 'settings'));

        $action = $isAnswer ? 'answer' : 'thread';
        $categories = $this->CurrentUser->getCategories()->getAll($action, 'list');
        $this->set('categories', $categories);
    }
}
