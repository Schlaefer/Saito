<?php

namespace Api\Controller;

use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use Saito\Exception\SaitoForbiddenException;

class ApiUsersController extends ApiAppController
{

    public $helpers = ['Api.Api'];

    public $saveKeysToOutput = [];

    /**
     * Login a user.
     *
     * @throws UnauthorizedException
     * @throws BadRequestException
     * @return void
     */
    public function login()
    {
        $this->CurrentUser->logout();

        if (!$this->request->getData('username')) {
            throw new BadRequestException(
                'Field `username` is missing.',
                1433238401
            );
        }

        if (!$this->request->getData('password')) {
            throw new BadRequestException(
                'Field `password` is missing.',
                1433238501
            );
        }

        /*
        @todo what was this fuck about?
        $this->request->data = [
            'username' => $this->request->getData('username'),
            'password' => $this->request->getData('password'),
            'remember_me' => !empty($this->request->getData('remember_me'))
        ];
        */

        $this->CurrentUser->login();

        if ($this->CurrentUser->isLoggedIn() !== true) {
            throw new SaitoForbiddenException(
                'Login failed. Check your username and password.'
            );
        }
    }

    /**
     * Logout a user.
     *
     * @throws BadRequestException
     * @throws SaitoForbiddenException
     * @return void
     */
    public function logout()
    {
        if (!$this->CurrentUser->isLoggedIn()) {
            throw new SaitoForbiddenException('You are not logged in.');
        }
        $userId = $this->request->getData('id');
        if (!$userId) {
            throw new BadRequestException('User id is missing.');
        }
        if ((int)$userId !== $this->CurrentUser->getId()) {
            throw new ForbiddenException(
                sprintf(
                    'Not allowed to logout user with id `%s`.',
                    $userId
                )
            );
        }
        $this->CurrentUser->logout();
    }

    /**
     * Mark postings as read.
     *
     * @throws \InvalidArgumentException
     * @return void
     */
    public function markasread()
    {
        $data = $this->request->getData();
        if (empty($data['id'])) {
            throw new BadRequestException('User id is missing.');
        }
        $_userId = $data['id'];
        if (!$this->CurrentUser->isLoggedIn() ||
            $this->CurrentUser->getId() != $_userId
        ) {
            throw new SaitoForbiddenException(
                sprintf(
                    'You are not authorized for user id `%s`.',
                    $_userId
                )
            );
        }
        if (!empty($data['last_refresh'])) {
            $timestamp = strtotime($data['last_refresh']);
            if ($timestamp === false) {
                throw new \InvalidArgumentException(
                    sprintf(
                        '`%s` is not a valid timestamp string.',
                        $timestamp
                    )
                );
            }
            $isNewer = $this->CurrentUser->LastRefresh->isNewerThan($timestamp);
            if (!$isNewer) {
                $this->CurrentUser->LastRefresh->set(
                    date("Y-m-d H:i:s", $timestamp)
                );
            }
        } else {
            $this->CurrentUser->LastRefresh->set('now');
        }
        $this->set('id', $_userId);
        $this->set('last_refresh', $this->CurrentUser->get('last_refresh'));
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
        $this->Auth->allow(['login', 'logout']);
    }
}
