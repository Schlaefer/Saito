<?php

namespace App\Controller\Admin;

class SmiliesController extends AdminsController
{
    /**
     * Show all smilies.
     *
     * @return void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['SmileyCodes'],
            'limit' => 1000, // limit high enough so that no paging should occur
            'order' => ['Smiley.order' => 'ASC']
        ];

        $this->set('smilies', $this->paginate());
    }

    /**
     * Add new smiley.
     *
     * @return void
     */
    public function add()
    {
        $smiley = $this->Smilies->newEntity();
        if ($this->request->is('post')) {
            $this->Smilies->patchEntity($smiley, $this->request->data);
            if ($this->Smilies->save($smiley)) {
                $this->Flash->set(
                    __('The smily has been saved'),
                    ['element' => 'success']
                );
                $this->redirect(array('action' => 'index'));

                return;
            } else {
                $this->Flash->set(
                    __('The smiley could not be saved. Please, try again.'),
                    ['element' => 'error']
                );
            }
        }
        $this->set(compact('smiley'));
    }

    /**
     * Edit smiley.
     *
     * @param null $id smiley-ID
     * @return void
     */
    public function edit($id = null)
    {
        if (!$id && empty($this->request->data)) {
            $this->Flash->set(__('Invalid smiley.'), ['element' => 'error']);
            $this->redirect(array('action' => 'index'));

            return;
        }

        $smiley = $this->Smilies->get($id);
        if (!empty($this->request->data)) {
            $this->Smilies->patchEntity($smiley, $this->request->data);
            if ($this->Smilies->save($smiley)) {
                $this->Flash->set(
                    __('The smily has been saved'),
                    ['element' => 'success']
                );
                $this->redirect(array('action' => 'index'));

                return;
            } else {
                $this->Flash->set(
                    __('The smiley could not be saved. Please, try again.'),
                    ['element' => 'error']
                );
            }
        }
        $this->set(compact('smiley'));
    }

    /**
     * Delete smiley.
     *
     * @param null $id Smiley-ID
     * @return void
     */
    public function delete($id = null)
    {
        if (!$id || !$this->Smilies->exists(['id' => $id])) {
            $this->Flash->set(__('Invalid smiley.'), ['element' => 'error']);
            $this->redirect(array('action' => 'index'));

            return;
        }
        $smiley = $this->Smilies->get($id);
        if ($this->Smilies->delete($smiley)) {
            $this->Flash->set(__('Smiley deleted.'), ['element' => 'success']);
            $this->redirect(array('action' => 'index'));

            return;
        }
        $this->Flash->set(__('Smily was not deleted.'), ['element' => 'error']);
        $this->redirect(['action' => 'index']);
    }
}
