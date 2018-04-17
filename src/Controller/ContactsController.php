<?php

namespace App\Controller;

use App\Form\ContactForm;
use App\Form\ContactFormOwner;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Event\Event;
use Cake\Form\Form;
use Cake\Http\Exception\BadRequestException;
use Cake\ORM\TableRegistry;
use Saito\Exception\Logger\ExceptionLogger;

class ContactsController extends AppController
{

    /**
     * {@inheritDoc}
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->showDisclaimer = true;
        $this->Auth->allow('owner');
    }

    /**
     * Contacts forum's owner via contact address
     *
     * @return void
     */
    public function owner()
    {
        $recipient = 'contact';
        if ($this->CurrentUser->isLoggedIn()) {
            $user = $this->CurrentUser;
            $sender = $user->getId();
            $this->request = $this->request->withData('sender_contact', $user->get('user_email'));
        } else {
            $senderContact = $this->request->getData('sender_contact');
            $sender = [$senderContact => $senderContact];
        }

        $this->_contact(new ContactFormOwner(), $recipient, $sender);
    }

    /**
     * Contacts individual user
     *
     * @param string $id user-ID
     * @return void
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
            'titleForPage',
            __('user_contact_title', $recipient->get('username'))
        );

        $sender = $this->CurrentUser->getId();
        $this->_contact(new ContactForm(), $recipient, $sender);
    }

    /**
     *  contact form validating and email sending
     *
     * @param Form $contact contact-form
     * @param mixed $recipient recipient
     * @param mixed $sender sender
     * @return \Cake\Network\Response|void
     */
    protected function _contact(Form $contact, $recipient, $sender)
    {
        if ($this->request->is('get')) {
            if ($this->request->getData('cc') === null) {
                $this->request = $this->request->withData('cc', true);
            }
        }

        if ($this->request->is('post')) {
            $isValid = $contact->validate($this->request->getData());
            if ($isValid) {
                try {
                    $email = [
                        'recipient' => $recipient,
                        'sender' => $sender,
                        'subject' => $this->request->getData('subject'),
                        'message' => $this->request->getData('text'),
                        'template' => 'user_contact',
                        'ccsender' => (bool)$this->request->getData('cc'),
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
