<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\ORM\Entity;

class SmileyCodesController extends AppController
{

    /**
     * List smiley-codes.
     *
     * @return void
     */
    public function index()
    {
        $this->paginate = ['contain' => ['Smilies'], 'limit' => 1000];
        $this->set('smileyCodes', $this->paginate());
    }

    /**
     * Add smiley-code.
     *
     * @return void
     */
    public function add()
    {
        $smiley = $this->SmileyCodes->newEntity();
        $this->_addEditCommon($smiley);
    }

    /**
     * Edit smiley-code.
     *
     * @param string $id smiley-code-ID
     * @return void
     */
    public function edit($id)
    {
        if (!$id && empty($this->request->data)) {
            $this->Flash->set(
                __('Invalid smiley code'),
                ['element' => 'error']
            );
            $this->redirect(['action' => 'index']);

            return;
        }
        $smiley = $this->SmileyCodes->get($id);
        $this->_addEditCommon($smiley);
    }

    /**
     * Code shared between add and edit.
     *
     * @param Entity $smiley smiley
     * @return void
     */
    protected function _addEditCommon(Entity $smiley)
    {
        if (!empty($this->request->data)) {
            $smiley = $this->SmileyCodes->patchEntity(
                $smiley,
                $this->request->data
            );
            if ($this->SmileyCodes->save($smiley)) {
                $this->Flash->set(
                    __('The smiley code has been saved'),
                    ['element' => 'success']
                );
                $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->set(
                    __('The smiley code could not be saved. Please, try again.'),
                    ['element' => 'error']
                );
            }
        }

        $smilies = $this->SmileyCodes->Smilies
            ->find(
                'list',
                [
                    'keyField' => 'id',
                    'valueField' => 'icon'
                ]
            )
            ->toArray();
        $this->set(compact('smiley', 'smilies'));
    }

    /**
     * Delete smiley-code.
     *
     * @param string $id smiley-code-ID
     * @return void
     */
    public function delete($id)
    {
        if (!$id) {
            $this->Flash->set(
                __('Invalid id for smiley code'),
                ['element' => 'error']
            );
            $this->redirect(['action' => 'index']);

            return;
        }
        $smiley = $this->SmileyCodes->get($id);
        if ($this->SmileyCodes->delete($smiley)) {
            $this->Flash->set(
                __('Smiley code deleted'),
                ['element' => 'error']
            );
            $this->redirect(['action' => 'index']);

            return;
        }
        $this->Flash->set(
            __('Smiley code was not deleted'),
            ['element' => 'error']
        );
        $this->redirect(['action' => 'index']);
    }
}
