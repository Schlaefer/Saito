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
use App\Model\Table\DraftsTable;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;
use Saito\Exception\SaitoForbiddenException;

/**
 * Endpoint for adding/POST and editing/PUT posting
 *
 * @property DraftsTable $Drafts
 */
class DraftsController extends ApiAppController
{
    /**
     * Adds a new draft
     *
     * @return void
     */
    public function add(): void
    {
        $data = $this->getRequest()->getData();
        $data['user_id'] = $this->CurrentUser->getId();
        $draft = $this->Drafts->newEntity(
            $data,
            ['fields' => ['pid', 'subject', 'text', 'user_id']]
        );

        $draft = $this->Drafts->save($draft);

        if (!$draft) {
            throw new BadRequestException();
        }

        $response = [
            'type' => 'drafts',
            'id' => $draft->get('id'),
            'attributes' => [
                'id' => $draft->get('id'),
            ],
        ];
        $this->set('data', $response);
        $this->set('_serialize', ['data']);
    }

    /**
     * Updates an existing draft.
     *
     * @param string $id Id of the draft to be updated.
     * @return void
     */
    public function edit(string $id)
    {
        $id = (int)$id;
        try {
            $draft = $this->Drafts->get($id);
        } catch (\Throwable $e) {
            throw new NotFoundException(sprintf('Draft %s not found', $id));
        }

        if ($draft->get('user_id') !== $this->CurrentUser->getId()) {
            throw new SaitoForbiddenException(
                sprintf('Attempt to access draft %s.', $id),
                ['CurrentUser' => $this->CurrentUser]
            );
        }

        $data = $this->getRequest()->getData();
        if (empty($data['text']) && empty($data['subject'])) {
            /// Don't keep empty drafts. Empty data deletes the draft.
            $this->Drafts->delete($draft);

            // Clear out the draft-id in the frontend, so a potential restarted
            // draft is going to trigger an add() in the frontend which starts a
            // new draft.
            $response = [
                'type' => 'drafts',
                'id' => null,
                'attributes' => [
                    'id' => null,
                ],
            ];
            $this->set('data', $response);
        } else {
            $this->Drafts->patchEntity(
                $draft,
                $data,
                ['fields' => ['subject', 'text']]
            );
            $this->Drafts->save($draft);
            $this->set('data', []);
        }
        $this->set('_serialize', ['data']);
    }
}
