<?php

namespace Saito\Contact;

use App\Model\Entity\User;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;

class SaitoEmailContact implements ContactInterface
{

    protected $name;

    protected $address;

    protected static $systemContacts = [
        'main' => 'Saito.Settings.forum_email',
        'contact' => 'Saito.Settings.email_contact',
        'register' => 'Saito.Settings.email_register',
        'system' => 'Saito.Settings.email_system'
    ];

    /**
     * Constructor.
     *
     * @param string|int|array $contact contact
     */
    public function __construct($contact)
    {
        /* resovle build in addresses */
        if (is_string($contact) && isset(static::$systemContacts[$contact])) {
            // @performance ?
            $this->address = Configure::read(static::$systemContacts[$contact]);
            $this->name = Configure::read('Saito.Settings.forum_name');

            return $this;
        }

        /* Cake array format */
        if (is_array($contact) && count($contact) === 1) {
            $this->address = key($contact);
            $this->name = current($contact);

            return $this;
        }

        /* resolve users */
        if (is_numeric($contact)) {
            $Users = TableRegistry::get('Users');
            $contact = $Users->find()
                ->select(['id', 'username', 'user_email'])
                ->where(['id' => (int)$contact])
                ->first();
        }

        if (!($contact instanceof User)) {
            throw new \RuntimeException();
        }

        $this->name = $contact->get('username');
        $this->address = $contact->get('user_email');

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        if (!$this->name) {
            throw new \RuntimeException;
        }

        return $this->name;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        if (!$this->address) {
            throw new \RuntimeException;
        }

        return $this->address;
    }

    /**
     * returns participant array (Cake mail array)
     *
     * @return array [<address> => <name>]
     */
    public function toCake()
    {
        return [$this->getAddress() => $this->getName()];
    }
}
