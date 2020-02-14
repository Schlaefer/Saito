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

use App\Form\BlockForm;
use Cake\Core\Configure;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;
use Cake\I18n\Time;
use Saito\App\Registry;
use Saito\Exception\Logger\ExceptionLogger;
use Saito\Exception\Logger\ForbiddenLogger;
use Saito\Exception\SaitoForbiddenException;
use Saito\User\Blocker\ManualBlocker;
use Saito\User\Permission\ResourceAI;
use Siezi\SimpleCaptcha\Model\Validation\SimpleCaptchaValidator;
use Stopwatch\Lib\Stopwatch;

/**
 * User controller
 */
class UsersController extends AppController
{
    /**
     * {@inheritDoc}
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Referer');
    }

    /**
     * Login user.
     *
     * @return void|\Cake\Http\Response
     */
    public function login()
    {
        $data = $this->request->getData();
        if (empty($data['username'])) {
            $logout = $this->_logoutAndComeHereAgain();
            if ($logout) {
                return $logout;
            }

            /// Show form to user.
            if ($this->getRequest()->getQuery('redirect', null)) {
                $this->Flash->set(
                    __('user.authe.required.exp'),
                    ['element' => 'warning', 'params' => ['title' => __('user.authe.required.t')]]
                );
            }

            return;
        }

        if ($this->AuthUser->login()) {
            // Redirect query-param in URL.
            $target = $this->getRequest()->getQuery('redirect');
            // AuthenticationService puts the full local path into the redirect
            // parameter, so we have to strip the base-path off again.
            $target = Router::normalize($target);
            // Referer from Request
            $target = $target ?: $this->referer(null, true);

            if (empty($target)) {
                $target = '/';
            }

            return $this->redirect($target);
        }

        /// error on login
        $username = $this->request->getData('username');
        /** @var \App\Model\Entity\User $User */
        $User = $this->Users->find()
            ->where(['username' => $username])
            ->first();

        $message = __('user.authe.e.generic');

        if (!empty($User)) {
            if (!$User->isActivated()) {
                $message = __('user.actv.ny');
            } elseif ($User->isLocked()) {
                $ends = $this->Users->UserBlocks
                    ->getBlockEndsForUser($User->getId());
                if ($ends) {
                    $time = new Time($ends);
                    $data = [
                        'name' => $username,
                        'end' => $time->timeAgoInWords(['accuracy' => 'hour']),
                    ];
                    $message = __('user.block.pubExpEnds', $data);
                } else {
                    $message = __('user.block.pubExp', $username);
                }
            }
        }

        // don't autofill password
        $this->setRequest($this->getRequest()->withData('password', ''));

        $Logger = new ForbiddenLogger();
        $Logger->write(
            "Unsuccessful login for user: $username",
            ['msgs' => [$message]]
        );

        $this->Flash->set($message, [
            'element' => 'error', 'params' => ['title' => __('user.authe.e.t')],
        ]);
    }

    /**
     * Logout user.
     *
     * @return void|\Cake\Http\Response
     */
    public function logout()
    {
        $request = $this->getRequest();
        $cookies = $request->getCookieCollection();
        foreach ($cookies as $cookie) {
            $cookie = $cookie->withPath($request->getAttribute('webroot'));
            $this->setResponse($this->getResponse()->withExpiredCookie($cookie));
        }

        $this->AuthUser->logout();
        $this->redirect('/');
    }

    /**
     * Register new user.
     *
     * @return void|\Cake\Http\Response
     */
    public function register()
    {
        $this->set('status', 'view');

        $this->AuthUser->logout();

        $tosRequired = Configure::read('Saito.Settings.tos_enabled');
        $this->set(compact('tosRequired'));

        $user = $this->Users->newEmptyEntity();
        $this->set('user', $user);

        if (!$this->request->is('post')) {
            $logout = $this->_logoutAndComeHereAgain();
            if ($logout) {
                return $logout;
            }

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
        $errors = $validator->validate($this->request->getData());

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
                    'viewVars' => ['user' => $user],
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
     * @throws \Cake\Http\Exception\BadRequestException
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
                ['direction' => 'desc'],
            ],
            'registered' => [__('registered'), ['direction' => 'desc']],
        ];
        $showBlocked = $this->CurrentUser->permission('saito.core.user.lock.view');
        if ($showBlocked) {
            $menuItems['user_lock'] = [
                __('user.set.lock.t'),
                ['direction' => 'desc'],
            ];
        }

        $this->paginate = $options = [
            'contain' => ['UserOnline'],
            'sortWhitelist' => array_keys($menuItems),
            'finder' => 'paginated',
            'limit' => 400,
            'order' => [
                'UserOnline.logged_in' => 'desc',
            ],
        ];
        $users = $this->paginate($this->Users);

        $showBottomNavigation = true;

        $this->set(compact('menuItems', 'showBottomNavigation', 'users'));
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
     * @return \Cake\Http\Response
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
                        $viewedUser->get('id'),
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
     * @return \Cake\Http\Response|void
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

        $id = (int)$id;

        /** @var \App\Model\Entity\User $user */
        $user = $this->Users->find()
            ->contain(
                [
                    'UserBlocks' => function ($q) {
                        return $q->find('assocUsers');
                    },
                    'UserOnline',
                ]
            )
            ->where(['Users.id' => (int)$id])
            ->first();

        if (empty($user)) {
            $this->Flash->set(__('Invalid user'), ['element' => 'error']);

            return $this->redirect('/');
        }

        $entriesShownOnPage = 20;
        $this->set(
            'lastEntries',
            $this->Users->Entries->getRecentPostings(
                $this->CurrentUser,
                ['user_id' => $id, 'limit' => $entriesShownOnPage]
            )
        );

        $this->set(
            'hasMoreEntriesThanShownOnPage',
            $user->numberOfPostings() - $entriesShownOnPage > 0
        );

        if ($this->CurrentUser->getId() === $id) {
            $ignores = $this->Users->UserIgnores->getAllIgnoredBy($id);
            $user->set('ignores', $ignores);
        }

        $blockForm = new BlockForm();
        $solved = $this->Users->countSolved($id);
        $this->set(compact('blockForm', 'solved', 'user'));
        $this->set('titleForLayout', $user->get('username'));
    }

    /**
     * Set user avatar.
     *
     * @param string $userId user-ID
     * @return void|\Cake\Http\Response
     */
    public function avatar($userId)
    {
        if (!$this->Users->exists($userId)) {
            throw new BadRequestException();
        }

        /** @var \App\Model\Entity\User $user */
        $user = $this->Users->get($userId);

        $permissionEditing = $this->CurrentUser->permission(
            'saito.core.user.edit',
            (new ResourceAI())->onRole($user->getRole())->onOwner($user->getId())
        );
        if (!$permissionEditing) {
            throw new \Saito\Exception\SaitoForbiddenException(
                "Attempt to edit user $userId.",
                ['CurrentUser' => $this->CurrentUser]
            );
        }

        if ($this->request->is('post') || $this->request->is('put')) {
            $data = [
                'avatar' => $this->request->getData('avatar'),
                'avatarDelete' => $this->request->getData('avatarDelete'),
            ];
            if (!empty($data['avatarDelete'])) {
                $data = [
                    'avatar' => null,
                    'avatar_dir' => null,
                ];
            }
            $patched = $this->Users->patchEntity($user, $data);
            $errors = $patched->getErrors();
            if (empty($errors) && $this->Users->save($patched)) {
                return $this->redirect(['action' => 'edit', $userId]);
            } else {
                $this->Flash->set(
                    __('The user could not be saved. Please, try again.'),
                    ['element' => 'error']
                );
            }
        }

        $this->set('user', $user);

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
     * @return \Cake\Http\Response|void
     */
    public function edit($id = null)
    {
        $this->viewBuilder()->setHelpers(['SpectrumColorpicker.SpectrumColorpicker']);

        /** @var \App\Model\Entity\User $user */
        $user = $this->Users->get($id);

        $permissionEditing = $this->CurrentUser->permission(
            'saito.core.user.edit',
            (new ResourceAI())->onRole($user->getRole())->onOwner($user->getId())
        );
        if (!$permissionEditing) {
            throw new \Saito\Exception\SaitoForbiddenException(
                sprintf('Attempt to edit user "%s".', $user->get('id')),
                ['CurrentUser' => $this->CurrentUser]
            );
        }

        if ($this->request->is('post') || $this->request->is('put')) {
            $data = $this->request->getData();
            $patched = $this->Users->patchEntity($user, $data);
            $errors = $patched->getErrors();
            if (empty($errors) && $this->Users->save($patched)) {
                return $this->redirect(['action' => 'view', $id]);
            }

            $this->Flash->set(
                __('The user could not be saved. Please, try again.'),
                ['element' => 'error']
            );
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
     * delete user
     *
     * @param string $id user-ID
     * @return \Cake\Http\Response|void
     */
    public function delete($id)
    {
        $id = (int)$id;
        /** @var \App\Model\Entity\User $readUser */
        $readUser = $this->Users->get($id);

        /// Check permission
        $permission = $this->CurrentUser->permission(
            'saito.core.user.delete',
            (new ResourceAI())->onRole($readUser->getRole())
        );
        if (!$permission) {
            throw new SaitoForbiddenException(
                'Not allowed to delete a user.',
                ['CurrentUser' => $this->CurrentUser, 'user_id' => $readUser->get('username')]
            );
        }

        $this->set('user', $readUser);

        $failure = false;
        if (!$this->request->getData('userdeleteconfirm')) {
            $failure = true;
            $this->Flash->set(__('user.del.fail.3'), ['element' => 'error']);
        } elseif ($this->CurrentUser->isUser($readUser)) {
            $failure = true;
            $this->Flash->set(__('user.del.fail.1'), ['element' => 'error']);
        }

        if (!$failure) {
            $result = $this->Users->deleteAllExceptEntries($id);
            if (empty($result)) {
                $failure = true;
                $this->Flash->set(__('user.del.fail.2'), ['element' => 'error']);
            }
        }

        if ($failure) {
            return $this->redirect(
                [
                    'prefix' => false,
                    'controller' => 'users',
                    'action' => 'view',
                    $id,
                ]
            );
        }

        $this->Flash->set(
            __('user.del.ok.m', $readUser->get('username')),
            ['element' => 'success']
        );

        return $this->redirect('/');
    }

    /**
     * Lock user.
     *
     * @return \Cake\Http\Response|void
     * @throws \Cake\Http\Exception\BadRequestException
     */
    public function lock()
    {
        $form = new BlockForm();
        if (!$form->validate($this->request->getData())) {
            throw new BadRequestException();
        }

        $id = (int)$this->request->getData('lockUserId');

        /** @var \App\Model\Entity\User $readUser */
        $readUser = $this->Users->get($id);

        $permission = $this->CurrentUser->permission(
            'saito.core.user.lock.set',
            (new ResourceAI())->onRole($readUser->getRole())
        );
        if (!$permission) {
            throw new SaitoForbiddenException(
                null,
                ['CurrentUser' => $this->CurrentUser]
            );
        }

        if ($this->CurrentUser->isUser($readUser)) {
            $message = __('You can\'t lock yourself.');
            $this->Flash->set($message, ['element' => 'error']);
        } else {
            try {
                $duration = (int)$this->request->getData('lockPeriod');
                $blocker = new ManualBlocker($this->CurrentUser->getId(), $duration);
                $status = $this->Users->UserBlocks->block($blocker, $id);
                if (!$status) {
                    throw new \Exception();
                }
                $message = __('User {0} is locked.', $readUser->get('username'));
                $this->Flash->set($message, ['element' => 'success']);
            } catch (\Exception $e) {
                $message = __('Error while locking.');
                $this->Flash->set($message, ['element' => 'error']);
            }
        }

        return $this->redirect($this->referer());
    }

    /**
     * Unblock user.
     *
     * @param string $id user-ID
     * @return void
     */
    public function unlock(string $id)
    {
        $id = (int)$id;

        /** @var \App\Model\Entity\User $user */
        $user = $this->Users
            ->find()
            ->matching('UserBlocks', function ($q) use ($id) {
                return $q->where(['UserBlocks.id' => $id]);
            })
            ->first();

        $permission = $this->CurrentUser->permission(
            'saito.core.user.lock.set',
            (new ResourceAI())->onRole($user->getRole())
        );
        if (!$permission) {
            throw new SaitoForbiddenException(
                null,
                ['CurrentUser' => $this->CurrentUser]
            );
        }

        if (!$this->Users->UserBlocks->unblock($id)) {
            $this->Flash->set(
                __('Error while unlocking.'),
                ['element' => 'error']
            );
        }

        $message = __('User {0} is unlocked.', $user->get('username'));
        $this->Flash->set($message, ['element' => 'success']);
        $this->redirect($this->referer());
    }

    /**
     * changes user password
     *
     * @param null $id user-ID
     * @return void
     * @throws \Saito\Exception\SaitoForbiddenException
     * @throws \Cake\Http\Exception\BadRequestException
     */
    public function changepassword($id = null)
    {
        if (empty($id)) {
            throw new BadRequestException();
        }

        /** @var \App\Model\Entity\User $user */
        $user = $this->Users->get($id);
        $allowed = $this->CurrentUser->isUser($user);
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
     * Directly set password for user
     *
     * @param string $id user-ID
     * @return \Cake\Http\Response|void
     */
    public function setpassword($id)
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->Users->get($id);

        $permissionResource = (new ResourceAI())->onRole($user->getRole());
        if (!$this->CurrentUser->permission('saito.core.user.password.set', $permissionResource)) {
            throw new SaitoForbiddenException(
                "Attempt to set password for user $id.",
                ['CurrentUser' => $this->CurrentUser]
            );
        }

        if ($this->getRequest()->is('post')) {
            $this->Users->patchEntity($user, $this->getRequest()->getData(), ['fields' => 'password']);

            if ($this->Users->save($user)) {
                $this->Flash->set(
                    __('user.pw.set.s'),
                    ['element' => 'success']
                );

                return $this->redirect(['controller' => 'users', 'action' => 'edit', $id]);
            }
            $errors = $user->getErrors();
            if (!empty($errors)) {
                $this->Flash->set(
                    __d('nondynamic', current(array_pop($errors))),
                    ['element' => 'error']
                );
            }
        }

        $this->set(compact('user'));
    }

    /**
     * View and set user role
     *
     * @param string $id User-ID
     * @return void|\Cake\Http\Response
     */
    public function role($id)
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->Users->get($id);
        $identifier = (new ResourceAI())->onRole($user->getRole());
        $unrestricted = $this->CurrentUser->permission('saito.core.user.role.set.unrestricted', $identifier);
        $restricted = $this->CurrentUser->permission('saito.core.user.role.set.restricted', $identifier);
        if (!$restricted && !$unrestricted) {
            throw new SaitoForbiddenException(
                null,
                ['CurrentUser' => $this->CurrentUser]
            );
        }

        /** @var \Saito\User\Permission\Permissions $Permissions */
        $Permissions = Registry::get('Permissions');

        $roles = $Permissions->getRoles()->get($this->CurrentUser->getRole(), false, $unrestricted);

        if ($this->getRequest()->is('put')) {
            $type = $this->getRequest()->getData('user_type');
            if (!in_array($type, $roles)) {
                throw new \InvalidArgumentException(
                    sprintf('User type "%s" is not available.', $type),
                    1573376871
                );
            }
            $patched = $this->Users->patchEntity($user, ['user_type' => $type]);

            $errors = $patched->getErrors();
            if (empty($errors)) {
                $this->Users->save($patched);

                return $this->redirect(['action' => 'edit', $user->get('id')]);
            }

            $msg = current(current($errors));
            $this->Flash->set($msg, ['element' => 'error']);
        }

        $this->set(compact('roles', 'user'));
    }

    /**
     * Set slidetab-order.
     *
     * @return \Cake\Http\Response
     * @throws \Cake\Http\Exception\BadRequestException
     */
    public function slidetabOrder()
    {
        if (!$this->request->is('ajax')) {
            throw new BadRequestException();
        }

        $order = $this->request->getData('slidetabOrder');
        if (!$order) {
            throw new BadRequestException();
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
     * Shows user's uploads
     *
     * @return void
     */
    public function uploads()
    {
    }

    /**
     * Set category for user.
     *
     * @param string|null $id category-ID
     * @return \Cake\Http\Response
     */
    public function setcategory(?string $id = null)
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
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        Stopwatch::start('Users->beforeFilter()');

        $unlocked = ['slidetabToggle', 'slidetabOrder'];
        $this->Security->setConfig('unlockedActions', $unlocked);

        $this->Authentication->allowUnauthenticated(['login', 'logout', 'register', 'rs']);
        $this->AuthUser->authorizeAction('register', 'saito.core.user.register');
        $this->AuthUser->authorizeAction('rs', 'saito.core.user.register');

        // Login form times-out and degrades user experience.
        // See https://github.com/Schlaefer/Saito/issues/339
        if (
            ($this->getRequest()->getParam('action') === 'login')
            && $this->components()->has('Security')
        ) {
            $this->components()->unload('Security');
        }

        Stopwatch::stop('Users->beforeFilter()');
    }

    /**
     * Logout user if logged in and create response to revisit logged out
     *
     * @return \Cake\Http\Response|null
     */
    protected function _logoutAndComeHereAgain(): ?Response
    {
        if (!$this->CurrentUser->isLoggedIn()) {
            return null;
        }
        $this->AuthUser->logout();

        return $this->redirect($this->getRequest()->getRequestTarget());
    }
}
