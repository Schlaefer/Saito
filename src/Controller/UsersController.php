<?php

namespace App\Controller;

use App\Lib\Controller\AuthSwitchTrait;
use Cake\Core\Configure;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\I18n\Time;
use Cake\Network\Exception\BadRequestException;
use Saito\Exception\Logger\ExceptionLogger;
use Saito\Exception\Logger\ForbiddenLogger;
use Saito\Exception\SaitoForbiddenException;
use Saito\String\Properize;
use Saito\User\Blocker\ManualBlocker;
use Saito\User\SaitoUser;
use Siezi\SimpleCaptcha\Model\Validation\SimpleCaptchaValidator;
use \Stopwatch\Lib\Stopwatch;


class UsersController extends AppController
{

    use AuthSwitchTrait;

    public $name = 'Users';

    public $helpers = [
        // @todo
//			'Farbtastic',
        'Map',
        'Posting',
        'Siezi/SimpleCaptcha.SimpleCaptcha',
        'Text'
    ];

    public function login()
    {
        $this->CurrentUser->logOut();

        //= just show form
        if (!$this->request->data('username')) {
            return;
        }

        //= successful login with request data
        if ($this->CurrentUser->login()) {
            if ($this->localReferer('action') === 'login') {
                $this->redirect($this->Auth->redirectUrl());
            } else {
                $this->redirect($this->referer());
            }

            return;
        }

        //= error on login
        $username = $this->request->data('username');
        $readUser = $this->Users->findByUsername($username)->first();

        $status = null;

        if (!empty($readUser)) {
            $User = new SaitoUser($readUser);
            $status = $User->isForbidden();
        }

        switch ($status) {
            case 'locked':
                $ends = $this->Users->UserBlocks
                    ->getBlockEndsForUser($User->getId());
                if ($ends) {
                    $time = new Time($ends);
                    $data = [
                        $username,
                        $time->timeAgoInWords(['accuracy' => 'hour'])
                    ];
                    $message = __('user.block.pubExpEnds', $data);
                } else {
                    $message = __('user.block.pubExp', $username);
                }
                break;
            case 'unactivated':
                $message = __(
                    'User %s is not activated yet.',
                    $readUser['User']['username']
                );
                break;
            default:
                $message = __('auth_loginerror');
        }

        // don't autofill password
        unset($this->request->data['User']['password']);

        $Logger = new ForbiddenLogger;
        $Logger->write(
            "Unsuccessful login for user: $username",
            ['msgs' => [$message]]
        );

        $this->Flash->set($message, ['key' => 'auth']);
    }

    public function logout()
    {
        $this->CurrentUser->logout();
    }

    public function register()
    {
        $this->set('status', 'view');

        $this->CurrentUser->logout();

        $tosRequired = Configure::read('Saito.Settings.tos_enabled');
        $this->set(compact('tosRequired'));

        $user = $this->Users->newEntity();
        $this->set('user', $user);

        if (!$this->request->is('post')) {
            return;
        }

        $data = $this->request->data;

        if (!$tosRequired) {
            $data['tos_confirm'] = true;
        }
        $tosConfirmed = $data['tos_confirm'];
        if (!$tosConfirmed) {
            return;
        }

        $data = $this->passwordAuthSwitch($data);

        $validator = new SimpleCaptchaValidator();
        $errors = $validator->errors($this->request->data);

        $user = $this->Users->register($data);
        $user->errors($errors);

        $errors = $user->errors();
        if (!empty($errors)) {
            // registering failed, show form again
            if (isset($errors['password'])) {
                // undo the passwordAuthSwitch() to display error message for the field
                // duplicate in admin controller user add
                $pwError['user_password'] = $errors['password'];
                $user->errors($pwError);
            }
            $user->set('tos_confirm', false);
            $this->set('user', $user);

            return;
        }

        // registered successfully
        try {
            $forumName = Configure::read('Saito.Settings.forum_name');
            $subject = __('register_email_subject', $forumName);
            $this->SaitoEmail->email(
                [
                    'recipient' => $user,
                    'subject' => $subject,
                    'sender' => 'register',
                    'template' => 'user_register',
                    'viewVars' => ['user' => $user]
                ]
            );
        } catch (\Exception $e) {
            $Logger = new ExceptionLogger();
            $Logger->write(
                'Registering email confirmation failed',
                ['e' => $e]
            );
            $this->set('status', 'fail: email');

            return;
        }

        $this->set('status', 'success');
    }

    /**
     * register success (user clicked link in confirm mail)
     *
     * @param $id
     * @throws BadRequestException
     */
    public function rs($id = null)
    {
        if (!$id) {
            throw new BadRequestException();
        }
        $code = $this->request->query('c');
        try {
            $activated = $this->Users->activate((int)$id, $code);
        } catch (\Exception $e) {
            $activated = false;
        }
        if (!$activated) {
            $activated = ['status' => 'fail'];
        }
        $this->set('status', $activated['status']);
    }

    public function index()
    {
        $menuItems = [
            'username' => [__('username_marking'), []],
            'user_type' => [__('user_type'), []],
            'UserOnline.logged_in' => [
                __('userlist_online'),
                ['direction' => 'desc']
            ],
            'registered' => [__('registered'), ['direction' => 'desc']]
        ];
        $showBlocked = Configure::read('Saito.Settings.block_user_ui');
        if ($showBlocked) {
            $menuItems['user_lock'] = [
                __('user.set.lock.t'),
                ['direction' => 'desc']
            ];
        }

        $this->paginate = $options = [
            'contain' => ['UserOnline'],
            'sortWhitelist' => array_keys($menuItems),
            'finder' => 'paginated',
            'limit' => 400,
            'order' => [
                'UserOnline.logged_in' => 'desc',
            ]
        ];
        $users = $this->paginate($this->Users);

        $this->set(compact('menuItems', 'users'));
    }

    public function ignore()
    {
        $this->request->allowMethod('POST');
        $blockedId = $this->request->data('id');
        $this->_ignore($blockedId, true);
    }

    public function unignore()
    {
        $this->request->allowMethod('POST');
        $blockedId = $this->request->data('id');
        $this->_ignore($blockedId, false);
    }

    protected function _ignore($blockedId, $set)
    {
        if (!$this->CurrentUser->isLoggedIn() || !is_numeric($blockedId)) {
            throw new BadRequestException();
        }
        $userId = $this->CurrentUser->getId();
        $this->User->id = $userId;
        if (!$this->User->exists($userId) || $userId == $blockedId) {
            throw new BadRequestException();
        }
        if ($set) {
            $this->User->Ignore->ignore($userId, $blockedId);
        } else {
            $this->User->Ignore->unignore($userId, $blockedId);
        }
        $this->redirect($this->referer());
    }

    public function map()
    {
        if (!Configure::read('Saito.Settings.map_enabled')) {
            $this->Flash->set(
                __('admin.setting.disabled', __('admin.feat.map')),
                ['template' => 'error']
            );
            $this->redirect('/');

            return;
        }
        $users = $this->Users->find(
            'all',
            [
                'conditions' => ['user_place_lat IS NOT' => null],
                'fields' => [
                    'id',
                    'username',
                    'user_place_lat',
                    'user_place_lng'
                ]
            ]
        );
        $this->set(compact('users'));
    }

    public function name($name = null)
    {
        if (!empty($name)) {
            $viewedUser = $this->Users->find()
                                      ->select(['id'])
                                      ->where(['username' => $name])
                                      ->first();
            if (!empty($viewedUser)) {
                $this->redirect(
                    [
                        'controller' => 'users',
                        'action' => 'view',
                        $viewedUser->get('id')
                    ]
                );

                return;
            }
        }
        $this->Flash->set(__('Invalid user'), ['element' => 'error']);
        $this->redirect('/');
    }

    public function view($id = null)
    {
        // redirect view/<username> to name/<username>
        if (!empty($id) && !is_numeric($id)) {
            $this->redirect(
                ['controller' => 'users', 'action' => 'name', $id]
            );

            return;
        }

        $viewedUser = $this->Users->find()
                                  ->contain(
                                      ['UserBlocks' => ['By'], 'UserOnline']
                                  )
                                  ->where(['Users.id' => $id])
                                  ->first();

        if ($id === null || empty($viewedUser)) {
            $this->Flash->set(__('Invalid user'), ['element' => 'error']);
            $this->redirect('/');

            return;
        }

        $entriesShownOnPage = 20;
        $this->set(
            'lastEntries',
            $this->Users->Entries->getRecentEntries(
                $this->CurrentUser,
                ['user_id' => $id, 'limit' => $entriesShownOnPage]
            )
        );

        $this->set(
            'hasMoreEntriesThanShownOnPage',
            ($viewedUser->numberOfPostings() - $entriesShownOnPage) > 0
        );

        if ($this->CurrentUser->getId() == $id) {
            // @todo 3.0
//				$viewedUser['User']['ignores'] = $this->User->Ignore->ignoredBy($id);
        }
        // @todo 3.0
        $viewedUser->set('solves_count', $this->Users->countSolved($id));
        $this->set('user', $viewedUser);
        $this->set(
            'title_for_layout',
            $viewedUser['User']['username']
        );
    }

    /**
     * @param null $id
     * @throws Saito\Exception\SaitoForbiddenException
     * @throws BadRequestException
     */
    public function edit($id = null)
    {
        if (!$id) {
            throw new BadRequestException;
        }
        if (!$this->_isEditingAllowed($this->CurrentUser, $id)) {
            throw new \Saito\Exception\SaitoForbiddenException(
                "Attempt to edit user $id.", [
                'CurrentUser' => $this->CurrentUser
            ]
            );
        }

        $user = $this->Users->get($id);

        if ($this->request->is('post') || $this->request->is('put')) {
            $data = $this->request->data;

            unset($data['id']);
            //= make sure only admin can edit these fields
            if (!$this->CurrentUser->permission('saito.core.user.edit')) {
                // @todo DRY: refactor this admin fields together with view
                unset($data['username'], $data['user_email'], $data['user_type']);
            }
            if (!empty($data['avatarDelete'])) {
                $data['avatar'] = null;
            }

            $user = $this->Users->patchEntity($user, $data);
            $errors = $user->errors();
            if (empty($errors) && $this->Users->save($user)) {
                $this->redirect(['action' => 'view', $id]);

                return;
            } else {
                $this->JsData->addAppJsMessage(
                    __('The user could not be saved. Please, try again.'),
                    ['type' => 'error']
                );
            }
        }

        $this->set('user', $user);
        $this->set(
            'title_for_layout',
            __(
                'Edit %s Profil',
                Properize::prop($user->get('username'))
            )
        );

        $themes = $this->Themes->getAvailable();
        $this->set('availableThemes', array_combine($themes, $themes));
    }

    public function admin_block()
    {
        $this->set('UserBlock', $this->User->UserBlock->getAll());
    }

    /**
     * @throws BadRequestException
     */
    public function lock()
    {
        if (!($this->modLocking)) {
            $this->redirect('/');

            return;
        }

        $id = (int)$this->request->data('User.lockUserId');
        if (!$id) {
            throw new BadRequestException;
        }

        try {
            $readUser = $this->Users->get($id);
        } catch (RecordNotFoundException $e) {
            $message = __('User not found.');
            $this->Flash->set($message, ['element' => 'error']);
            $this->redirect('/');

            return;
        }

        $editedUser = new SaitoUser($readUser);

        if ($id === $this->CurrentUser->getId()) {
            $message = __('You can\'t lock yourself.');
            $this->Flash->set($message, ['element' => 'error']);
        } elseif ($editedUser->getRole() === 'admin') {
            $message = __('You can\'t lock administrators.');
            $this->Flash->set($message, ['element' => 'error']);
        } else {
            try {
                $duration = (int)$this->request->data('User.lockPeriod');
                $status = $this->Users->UserBlocks->block(
                    new ManualBlocker,
                    $id,
                    [
                        'adminId' => $this->CurrentUser->getId(),
                        'duration' => $duration
                    ]
                );
                $username = $readUser['User']['username'];
                if ($status === true) {
                    $message = __('User %s is locked.', $username);
                } else {
                    $message = __('User %s is unlocked.', $username);
                }
                $this->Flash->set($message, ['element' => 'success']);
            } catch (Exception $e) {
                $message = __('Error while un/locking.');
                $this->Flash->set($message, ['element' => 'error']);
            }
        }
        $this->redirect($this->referer());
    }

    public function unlock($id)
    {
        if (!$id || !$this->modLocking) {
            throw new BadRequestException;
        }
        // @todo 3.0
        if (!$this->User->UserBlock->unblock($id)) {
            $this->Session->setFlash(
                __('Error while unlocking.'),
                'flash/error'
            );
        }
        $this->redirect($this->referer());
    }

    /**
     * changes user password
     *
     * @param null $id
     * @throws \Saito\Exception\SaitoForbiddenException
     * @throws BadRequestException
     */
    public function changepassword($id = null)
    {
        if (!$id) {
            throw new BadRequestException();
        }

        $user = $this->Users->get($id);
        $allowed = $this->_isEditingAllowed($this->CurrentUser, $id);
        if (empty($user) || !$allowed) {
            throw new SaitoForbiddenException(
                "Attempt to change password for user $id.",
                ['CurrentUser' => $this->CurrentUser]
            );
        }
        $this->set('userId', $id);
        $this->set('username', $user->get('username'));

        //= just show empty form
        if (empty($this->request->data)) {
            return;
        }

        //= process submitted form
        $this->request->data = $this->passwordAuthSwitch($this->request->data);
        $data = [
            'password_old' => $this->request->data['password_old'],
            'password' => $this->request->data['password'],
            'password_confirm' => $this->request->data['password_confirm']
        ];
        $this->Users->patchEntity($user, $data);
        $success = $this->Users->save($user);

        if ($success) {
            $this->Flash->set(
                __('change_password_success'),
                ['element' => 'success']
            );
            $this->redirect(['controller' => 'users', 'action' => 'edit', $id]);

            return;
        }

        $errors = $user->errors();
        if (!empty($errors)) {
            $this->Flash->set(
                __d('nondynamic', current(array_pop($errors))),
                ['element' => 'error']
            );
        }

        // unset all autofill form data
        $this->request->data = [];
    }

    /**
     * @throws BadRequestException
     */
    private function __ajaxBeforeFilter()
    {
        if (!$this->request->is('ajax')) {
            throw new BadRequestException;
        }
        $this->autoRender = false;
    }

    /**
     * toggles slidetabs open/close
     *
     * @return $this|mixed
     * @throws BadRequestException
     */
    public function slidetab_toggle()
    {
        $this->__ajaxBeforeFilter();

        $toggle = $this->request->data('slidetabKey');
        $allowed = [
            'show_userlist',
            'show_recentposts',
            'show_recententries',
            'show_shoutbox'
        ];
        if (!$toggle || !in_array($toggle, $allowed)) {
            throw new BadRequestException();
        }

        $userId = $this->CurrentUser->getId();
        $newValue = $this->Users->toggle($userId, $toggle);
        $this->CurrentUser[$toggle] = $newValue;
        $this->response->body($newValue);

        return $this->response;
    }

    /**
     * sets slidetab-order
     *
     * @return bool
     * @throws BadRequestException
     */
    public function slidetab_order()
    {
        $this->__ajaxBeforeFilter();

        $order = $this->request->data('slidetabOrder');
        if (!$order) {
            throw new BadRequestException;
        }

        $allowed = $this->viewVars['slidetabs'];
        $order = array_filter(
            $order,
            function ($item) use ($allowed) {
                return in_array($item, $allowed);
            }
        );
        $order = serialize($order);

        $userId = $this->CurrentUser->getId();
        $user = $this->Users->get($userId);
        $this->Users->patchEntity($user, ['slidetab_order' => $order]);
        $this->Users->save($user);

        $this->CurrentUser['slidetab_order'] = $order;

        $this->response->body(true);

        return $this->response;
    }

    /**
     * @param null $id
     *
     * @throws ForbiddenException
     */
    public function setcategory($id = null)
    {
        if (!$this->CurrentUser->isLoggedIn()) {
            throw new ForbiddenException();
        }
        $userId = $this->CurrentUser->getId();
        if ($id === 'all') {
            $this->Users->setCategory($userId, 'all');
        } elseif (!$id && $this->request->data) {
            $data = $this->request->data('CatChooser');
            $this->Users->setCategory($userId, $data);
        } else {
            $this->Users->setCategory($userId, $id);
        }
        $this->redirect($this->referer());
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        Stopwatch::start('Users->beforeFilter()');

        // @todo 3.0 CSRF protection
        $this->Security->config(
            'unlockedActions',
            ['slidetab_toggle', 'slidetab_order']
        );

        $this->Auth->allow(['login', 'register', 'rs']);
        $this->modLocking = $this->CurrentUser->permission(
            'saito.core.user.block'
        );
        $this->set('modLocking', $this->modLocking);

        Stopwatch::stop('Users->beforeFilter()');
    }

    /**
     * Checks if the current user is allowed to edit user $userId
     *
     * @param SaitoUser $CurrentUser
     * @param int $userId
     * @return type
     */
    protected function _isEditingAllowed(
        \Saito\User\ForumsUserInterface $CurrentUser,
        $userId
    ) {
        if ($CurrentUser->permission('saito.core.user.edit')) {
            return true;
        }

        return $CurrentUser->getId() === (int)$userId;
    }

}
