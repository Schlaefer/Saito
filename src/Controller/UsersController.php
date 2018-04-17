<?php

namespace App\Controller;

use App\Form\BlockForm;
use App\Model\Entity\User;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use Cake\I18n\Time;
use Cake\Network\Response;
use Saito\Exception\Logger\ExceptionLogger;
use Saito\Exception\Logger\ForbiddenLogger;
use Saito\Exception\SaitoForbiddenException;
use Saito\User\Blocker\ManualBlocker;
use Saito\User\ForumsUserInterface;
use Saito\User\SaitoUser;
use Siezi\SimpleCaptcha\Model\Validation\SimpleCaptchaValidator;
use \Stopwatch\Lib\Stopwatch;

class UsersController extends AppController
{
    public $helpers = [
        'SpectrumColorpicker.SpectrumColorpicker',
        'Map',
        'Posting',
        'Siezi/SimpleCaptcha.SimpleCaptcha',
        'Text'
    ];

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Referer');
    }

    /**
     * Login user.
     *
     * @return void|\Cake\Network\Response
     */
    public function login()
    {
        $this->CurrentUser->logOut();

        $data = $this->request->getData();
        //= just show form
        if (empty($data['username'])) {
            return;
        }

        //= successful login with request data
        if ($this->CurrentUser->login()) {
            if ($this->Referer->wasAction('login')) {
                return $this->redirect($this->Auth->redirectUrl());
            } else {
                return $this->redirect($this->referer());
            }
        }

        //= error on login
        $username = $this->request->getData('username');
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
                    'User {0} is not activated yet.',
                    [$readUser->get('username')]
                );
                break;
            default:
                $message = __('auth_loginerror');
        }

        // don't autofill password
        $this->setRequest($this->getRequest()->withData('password', ''));

        $Logger = new ForbiddenLogger;
        $Logger->write(
            "Unsuccessful login for user: $username",
            ['msgs' => [$message]]
        );

        $this->Flash->set($message, ['key' => 'auth']);
    }

    /**
     * Logout user.
     *
     * @return void
     */
    public function logout()
    {
        $this->CurrentUser->logout();
    }

    /**
     * Register new user.
     *
     * @return void
     */
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

        $data = $this->request->getData();

        if (!$tosRequired) {
            $data['tos_confirm'] = true;
        }
        $tosConfirmed = $data['tos_confirm'];
        if (!$tosConfirmed) {
            return;
        }

        $validator = new SimpleCaptchaValidator();
        $errors = $validator->errors($this->request->getData());

        $user = $this->Users->register($data);
        $user->setErrors($errors);

        $errors = $user->getErrors();
        if (!empty($errors)) {
            // registering failed, show form again
            if (isset($errors['password'])) {
                $user->setErrors($errors);
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
     * @param string $id user-ID
     * @return void
     * @throws BadRequestException
     */
    public function rs($id = null)
    {
        if (!$id) {
            throw new BadRequestException();
        }
        $code = $this->request->getQuery('c');
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

    /**
     * Show list of all users.
     *
     * @return void
     */
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

    /**
     * Ignore user.
     *
     * @return void
     */
    public function ignore()
    {
        $this->request->allowMethod('POST');
        $blockedId = (int)$this->request->getData('id');
        $this->_ignore($blockedId, true);
    }

    /**
     * Unignore user.
     *
     * @return void
     */
    public function unignore()
    {
        $this->request->allowMethod('POST');
        $blockedId = (int)$this->request->getData('id');
        $this->_ignore($blockedId, false);
    }

    /**
     * Mark user as un-/ignored
     *
     * @param int $blockedId user to ignore
     * @param bool $set block or unblock
     * @return \Cake\Network\Response
     */
    protected function _ignore($blockedId, $set)
    {
        $userId = $this->CurrentUser->getId();
        if ((int)$userId === (int)$blockedId) {
            throw new BadRequestException();
        }
        if ($set) {
            $this->Users->UserIgnores->ignore($userId, $blockedId);
        } else {
            $this->Users->UserIgnores->unignore($userId, $blockedId);
        }

        return $this->redirect($this->referer());
    }

    /**
     * View user-map.
     *
     * @return void
     */
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

    /**
     * Show user with profile $name
     *
     * @param string $name username
     * @return void
     */
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

    /**
     * View user profile.
     *
     * @param null $id user-ID
     * @return \Cake\Network\Response|void
     */
    public function view($id = null)
    {
        // redirect view/<username> to name/<username>
        if (!empty($id) && !is_numeric($id)) {
            $this->redirect(
                ['controller' => 'users', 'action' => 'name', $id]
            );

            return;
        }

        $user = $this->Users->find()
            ->contain(
                [
                    'UserBlocks' => function ($q) {
                        return $q->find('assocUsers');
                    },
                    'UserOnline'
                ]
            )
            ->where(['Users.id' => $id])
            ->first();

        if ($id === null || empty($user)) {
            $this->Flash->set(__('Invalid user'), ['element' => 'error']);

            return $this->redirect('/');
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
            ($user->numberOfPostings() - $entriesShownOnPage) > 0
        );

        if ($this->CurrentUser->isUser($id)) {
            $ignores = $this->Users->UserIgnores->getAllIgnoredBy($id);
            $user->set('ignores', $ignores);
        }

        $isEditingAllowed = $this->_isEditingAllowed($this->CurrentUser, $id);

        $blockForm = new BlockForm();
        $solved = $this->Users->countSolved($id);
        $this->set(compact('blockForm', 'isEditingAllowed', 'solved', 'user'));
        $this->set('titleForLayout', $user->get('username'));
    }

    /**
     * Set user avatar.
     *
     * @param string $userId user-ID
     * @return void|\Cake\Network\Response
     */
    public function avatar($userId = null)
    {
        $data = [];
        if ($this->request->is('post') || $this->request->is('put')) {
            $data = [
                'avatar' => $this->request->getData('avatar'),
                'avatarDelete' => $this->request->getData('avatarDelete')
            ];
            if (!empty($data['avatarDelete'])) {
                $data['avatar'] = null;
            }
        }
        $user = $this->_edit($userId, $data);
        if ($user instanceof Response) {
            return $user;
        }

        $this->set(
            'titleForPage',
            __('user.avatar.edit.t', [$user->get('username')])
        );
    }

    /**
     * Edit user.
     *
     * @param null $id user-ID
     *
     * @return \Cake\Network\Response|void
     */
    public function edit($id = null)
    {
        $data = [];
        if ($this->request->is('post') || $this->request->is('put')) {
            $data = $this->request->getData();
            unset($data['id']);
            //= make sure only admin can edit these fields
            if (!$this->CurrentUser->permission('saito.core.user.edit')) {
                // @td DRY: refactor this admin fields together with view
                unset($data['username'], $data['user_email'], $data['user_type']);
            }
        }
        $user = $this->_edit($id, $data);
        if ($user instanceof Response) {
            return $user;
        }

        $this->set('user', $user);
        $this->set(
            'titleForPage',
            __('user.edit.t', [$user->get('username')])
        );

        $availableThemes = $this->Themes->getAvailable($this->CurrentUser);
        $availableThemes = array_combine($availableThemes, $availableThemes);
        $currentTheme = $this->Themes->getThemeForUser($this->CurrentUser);
        $this->set(compact('availableThemes', 'currentTheme'));
    }

    /**
     * Handle user edit core. Retrieve user or patch if data is passed.
     *
     * @param string $userId user-ID
     * @param array|null $data datat to update the user
     *
     * @return \Cake\Network\Response|User
     */
    protected function _edit($userId, array $data = null)
    {
        if (!$this->_isEditingAllowed($this->CurrentUser, $userId)) {
            throw new \Saito\Exception\SaitoForbiddenException(
                "Attempt to edit user $userId.",
                ['CurrentUser' => $this->CurrentUser]
            );
        }
        if (!$this->Users->exists($userId)) {
            throw new BadRequestException;
        }
        $user = $this->Users->get($userId);

        if ($data) {
            $user = $this->Users->patchEntity($user, $data);
            $errors = $user->getErrors();
            if (empty($errors) && $this->Users->save($user)) {
                return $this->redirect(['action' => 'view', $userId]);
            } else {
                $this->JsData->addAppJsMessage(
                    __('The user could not be saved. Please, try again.'),
                    ['type' => 'error']
                );
            }
        }
        $this->set('user', $user);

        return $user;
    }

    /**
     * Lock user.
     *
     * @return \Cake\Network\Response|void
     * @throws BadRequestException
     */
    public function lock()
    {
        $form = new BlockForm();
        if (!$this->modLocking || !$form->validate($this->request->getData())) {
            throw new BadRequestException;
        }

        $id = (int)$this->request->getData('lockUserId');
        if (!$this->Users->exists($id)) {
            $message = __('User not found.');
            $this->Flash->set($message, ['element' => 'error']);

            return $this->redirect('/');
        }
        $readUser = $this->Users->get($id);

        if ($id === $this->CurrentUser->getId()) {
            $message = __('You can\'t lock yourself.');
            $this->Flash->set($message, ['element' => 'error']);
        } elseif ($readUser->getRole() === 'admin') {
            $message = __('You can\'t lock administrators.');
            $this->Flash->set($message, ['element' => 'error']);
        } else {
            try {
                $duration = (int)$this->request->getData('lockPeriod');
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
                    $message = __('User {0} is locked.', [$username]);
                } else {
                    $message = __('User {0} is unlocked.', [$username]);
                }
                $this->Flash->set($message, ['element' => 'success']);
            } catch (Exception $e) {
                $message = __('Error while un/locking.');
                $this->Flash->set($message, ['element' => 'error']);
            }
        }
        $this->redirect($this->referer());
    }

    /**
     * Unblock user.
     *
     * @param string $id user-ID
     * @return void
     */
    public function unlock($id)
    {
        if (!$id || !$this->modLocking) {
            throw new BadRequestException;
        }
        if (!$this->Users->UserBlocks->unblock($id)) {
            $this->Flash->set(
                __('Error while unlocking.'),
                ['element' => 'error']
            );
        }
        $this->redirect($this->referer());
    }

    /**
     * changes user password
     *
     * @param null $id user-ID
     * @return void
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
        if (empty($this->request->getData())) {
            return;
        }

        $formFields = ['password', 'password_old', 'password_confirm'];

        //= process submitted form
        $data = [];
        foreach ($formFields as $field) {
            $data[$field] = $this->request->getData($field);
        }
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

        $errors = $user->getErrors();
        if (!empty($errors)) {
            $this->Flash->set(
                __d('nondynamic', current(array_pop($errors))),
                ['element' => 'error']
            );
        }

        //= unset all autofill form data
        foreach ($formFields as $field) {
            $this->request = $this->request->withoutData($field);
        }
    }

    /**
     * toggles slidetabs open/close
     *
     * @return $this|mixed
     * @throws BadRequestException
     */
    public function slidetabToggle()
    {
        if (!$this->request->is('ajax')) {
            throw new BadRequestException;
        }

        $toggle = $this->request->getData('slidetabKey');
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
        $this->CurrentUser->set($toggle, $newValue);
        $this->response = $this->response->withStringBody($newValue);

        return $this->response;
    }

    /**
     * Set slidetab-order.
     *
     * @return \Cake\Network\Response
     * @throws BadRequestException
     */
    public function slidetabOrder()
    {
        if (!$this->request->is('ajax')) {
            throw new BadRequestException;
        }

        $order = $this->request->getData('slidetabOrder');
        if (!$order) {
            throw new BadRequestException;
        }

        $allowed = $this->Slidetabs->getAvailable();
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

        $this->CurrentUser->set('slidetab_order', $order);

        $this->response = $this->response->withStringBody(true);

        return $this->response;
    }

    /**
     * Set category for user.
     *
     * @param null $id category-ID
     * @return \Cake\Network\Response
     * @throws ForbiddenException
     */
    public function setcategory($id = null)
    {
        $userId = $this->CurrentUser->getId();
        if ($id === 'all') {
            $this->Users->setCategory($userId, 'all');
        } elseif (!$id && $this->request->getData()) {
            $data = $this->request->getData('CatChooser');
            $this->Users->setCategory($userId, $data);
        } else {
            $this->Users->setCategory($userId, $id);
        }

        return $this->redirect($this->referer());
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
        Stopwatch::start('Users->beforeFilter()');

        $unlocked = ['slidetabToggle', 'slidetabOrder'];
        $this->Security->setConfig('unlockedActions', $unlocked);

        $this->Auth->allow(['login', 'register', 'rs']);
        $this->modLocking = $this->CurrentUser
            ->permission('saito.core.user.block');
        $this->set('modLocking', $this->modLocking);

        Stopwatch::stop('Users->beforeFilter()');
    }

    /**
     * Checks if the current user is allowed to edit user $userId
     *
     * @param ForumsUserInterface $CurrentUser user
     * @param int $userId user-ID
     * @return bool
     */
    protected function _isEditingAllowed(ForumsUserInterface $CurrentUser, $userId)
    {
        if ($CurrentUser->permission('saito.core.user.edit')) {
            return true;
        }

        return $CurrentUser->isUser($userId);
    }
}
