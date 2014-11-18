<?php

namespace App\Controller;

use App\Form\ContactForm;
use App\Form\ContactFormOwner;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\Form\Form;
use Cake\Network\Exception\BadRequestException;
use Cake\ORM\TableRegistry;
use Saito\Exception\Logger\ExceptionLogger;

class ContactsController extends AppController
{

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->showDisclaimer = true;
        $this->Auth->allow('owner');
    }

    /**
     * Contacts forum's owner via contact address
     */
    public function owner()
    {
        $recipient = 'contact';
        if ($this->CurrentUser->isLoggedIn()) {
            $user = $this->CurrentUser;
            $sender = $user->getId();
            $this->request->data('sender_contact', $user['user_email']);
        } else {
            $senderContact = $this->request->data('sender_contact');
            $sender = [$senderContact => $senderContact];
        }

        $this->_contact(new ContactFormOwner(), $recipient, $sender);
    }

    /**
     * Contacts individual user
     *
     * @param null $id
     * @throws \InvalidArgumentException
     * @throws BadRequestException
     */
    public function user($id = null)
    {
        if (empty($id) || !$this->CurrentUser->isLoggedIn()) {
            throw new BadRequestException();
        }

        $Users = TableRegistry::get('Users');
        try {
            $recipient = $Users->get($id);
        } catch (RecordNotFoundException $e) {
            throw new BadRequestException();
        }
        $this->set('user', $recipient);

        if (!$recipient->get('personal_messages')) {
            throw new BadRequestException();
        }

        $this->set(
            'title_for_page',
            __('user_contact_title', $recipient->get('username'))
        );

        $sender = $this->CurrentUser->getId();
        $this->_contact(new ContactForm(), $recipient, $sender);
    }

    /**
     *  contact form validating and email sending
     *
     * @param Form $contact
     * @param $recipient
     * @param $sender
     * @return \Cake\Network\Response|void
     */
    protected function _contact(Form $contact, $recipient, $sender)
    {
        if ($this->request->is('get')) {
            if ($this->request->data('cc') === null) {
                $this->request->data('cc', true);
            }
        }

        if ($this->request->is('post')) {
            $isValid = $contact->validate($this->request->data);
            if ($isValid) {
                try {
                    $email = [
                        'recipient' => $recipient,
                        'sender' => $sender,
                        'subject' => $this->request->data('subject'),
                        'message' => $this->request->data('text'),
                        'template' => 'user_contact',
                        'ccsender' => (bool)$this->request->data('cc'),
                    ];
                    $this->SaitoEmail->email($email);
                    $message = __('Message was send.');
                    $this->Flash->set($message, ['element' => 'success']);

                    return $this->redirect('/');
                } catch (\Exception $e) {
                    $Logger = new ExceptionLogger();
                    $Logger->write('Contact email failed', ['e' => $e]);
                    $message = $e->getMessage();
                    $message = __('Message couldn\'t be send: {0}', $message);
                    $this->Flash->set($message, ['element' => 'error']);
                }
            }
        }

        $this->set(compact('contact'));
    }

}
