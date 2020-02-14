<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Admin\Controller;

/**
 * @property \App\Model\Table\SmiliesTable $Smilies
 */
class SmiliesController extends AdminAppController
{
    /**
     * {@inheritDoc}
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadModel('Smilies');
    }

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
            'order' => ['Smiley.order' => 'ASC'],
        ];

        $this->set('smilies', $this->paginate($this->Smilies));
    }

    /**
     * Add new smiley.
     *
     * @return void
     */
    public function add()
    {
        $smiley = $this->Smilies->newEmptyEntity();
        if ($this->request->is('post')) {
            $this->Smilies->patchEntity($smiley, $this->request->getData());
            if ($this->Smilies->save($smiley)) {
                $this->Flash->set(
                    __('The smily has been saved'),
                    ['element' => 'success']
                );
                $this->redirect(['action' => 'index']);

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
        if (empty($id) && empty($this->request->getData())) {
            $this->Flash->set(__('Invalid smiley.'), ['element' => 'error']);
            $this->redirect(['action' => 'index']);

            return;
        }

        $smiley = $this->Smilies->get($id);
        if (!empty($this->request->getData())) {
            $this->Smilies->patchEntity($smiley, $this->request->getData());
            if ($this->Smilies->save($smiley)) {
                $this->Flash->set(
                    __('The smily has been saved'),
                    ['element' => 'success']
                );
                $this->redirect(['action' => 'index']);

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
        if (empty($id) || !$this->Smilies->exists(['id' => $id])) {
            $this->Flash->set(__('Invalid smiley.'), ['element' => 'error']);
            $this->redirect(['action' => 'index']);

            return;
        }
        $smiley = $this->Smilies->get($id);
        if ($this->Smilies->delete($smiley)) {
            $this->Flash->set(__('Smiley deleted.'), ['element' => 'success']);
            $this->redirect(['action' => 'index']);

            return;
        }
        $this->Flash->set(__('Smily was not deleted.'), ['element' => 'error']);
        $this->redirect(['action' => 'index']);
    }
}
