<?php

namespace App\Controller\Admin;

use App\Controller\AppController;

class UsersController extends AppController
{
    public $actionAuthConfig = [
        'delete' => 'mod'
    ];

    /**
     * List all users.
     *
     * @return void
     */
    public function index()
    {
        $data = $this->Users->find()
            ->select(
                [
                    'id',
                    'username',
                    'user_type',
                    'user_email',
                    'registered',
                    'user_lock'
                ]
            )
            ->order(['username' => 'asc'])
            ->all();
        $this->set('users', $data);
    }

    /**
     * add user
     *
     * @return \Cake\Network\Response|void
     */
    public function add()
    {
        if (!$this->request->is('post') && empty($this->request->getData())) {
            $user = $this->Users->newEntity();
        } else {
            $user = $this->Users->register($this->request->getData(), true);
            if ($user && empty($errors)) {
                $this->Flash->set(__('user.admin.add.success'), ['element' => 'success']);

                return $this->redirect(['prefix' => false, 'action' => 'view', $user->get('id')]);
            } else {
                $this->Flash->set(__('user.admin.add.error'), ['element' => 'error']);
            }
        }
        $this->set('user', $user);
    }

    /**
     * delete user
     *
     * @param string $id user-ID
     * @return \Cake\Network\Response|void
     */
    public function delete($id)
    {
        $id = (int)$id;
        $exists = $this->Users->exists($id);
        if (!$exists) {
            $this->Flash->set(__('User not found.'), ['element' => 'error']);

            return $this->redirect('/');
        }
        $readUser = $this->Users->get($id);

        if ($this->request->is('post') && $this->request->getData('modeDelete')) {
            if ($id === $this->CurrentUser->getId()) {
                $this->Flash->set(
                    __("You can't delete yourself."),
                    ['element' => 'error']
                );
            } elseif ($id === 1) {
                $this->Flash->set(
                    __("You can't delete the installation account."),
                    ['element' => 'error']
                );
            } elseif (!$this->CurrentUser->permission('saito.core.user.delete')) {
                $this->Flash->set(
                    __("You are not authorized to delete a user."),
                    ['element' => 'error']
                );
            } elseif ($this->Users->deleteAllExceptEntries($id)) {
                $this->Flash->set(
                    __('User {0} deleted.', $readUser->get('username')),
                    ['element' => 'success']
                );

                return $this->redirect('/');
            } else {
                $this->Flash->set(
                    __("Couldn't delete user."),
                    ['element' => 'error']
                );
            }

            return $this->redirect(
                [
                    'prefix' => false,
                    'controller' => 'users',
                    'action' => 'view',
                    $id
                ]
            );
        }
        $this->set('user', $readUser);
    }

    /**
     * List all blocked users.
     *
     * @return void
     */
    public function block()
    {
        $this->set('UserBlock', $this->Users->UserBlocks->getAll());
    }
}
