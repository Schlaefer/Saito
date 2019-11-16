<?php

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Shell;

use App\Model\Table\EntriesTable;
use App\Model\Table\UsersTable;
use Cake\Console\Shell;
use Saito\App\Registry;
use Saito\User\CurrentUser\CurrentUserFactory;
use Saito\User\CurrentUser\CurrentUserInterface;

/**
 * Creates dummy data for development
 *
 * @property EntriesTable $Entries
 * @property UsersTable $Users
 */
class SaitoDummyDataShell extends Shell
{
    public $uses = ['Entry', 'User'];

    protected $_Categories = null;

    /** @var array */
    protected $_Users;

    protected $_text = null;

    protected $_Threads = [];

    protected $_users = [
        'Aaron',
        'Alex',
        'Amy',
        'Ana-Lucia',
        'Anthony',
        'Ben',
        'Bernard',
        'Boone',
        'Carmen',
        'Carole',
        'Charles',
        'Charlie',
        'Charlotte',
        'Christian',
        'Claire',
        'Daniel',
        'Danielle',
        'Desmond',
        'Dogen',
        'Eko',
        'Eloise',
        'Ethan',
        'Frank',
        'Frogurt',
        'George',
        'Gina',
        'Horace',
        'Hugo',
        'Ilana',
        'Jack',
        'Jacob',
        'James',
        'Jin',
        'John',
        'Juliet',
        'Kate',
        'Kelvin',
        'Liam',
        'Libby',
        'Martin',
        'Maninbla',
        'Michael',
        'Michelle',
        'Miles',
        'Nadia',
        'Naomi',
        'Nikki',
        'Omar',
        'Paulo',
        'Penny',
        'Pierre',
        'Richard',
        'Sarah',
        'Sayid',
        'Shannon',
        'Stuart',
        'Sun',
        'Teresa',
        'Tom',
        'Walt'
    ];

    /**
     * {@inheritDoc}
     */
    public function initialize()
    {
        parent::initialize();
        Registry::initialize();
        $this->loadModel('Entries');
        $this->loadModel('Users');
    }

    /**
     * {@inheritDoc}
     */
    public function main()
    {
        $this->generateUsers();
        $this->generatePostings();
    }

    /**
     * Generate postings
     *
     * @return void
     */
    public function generatePostings()
    {
        $nPostings = (int)$this->in(
            'Number of postings to generate?',
            null,
            '100'
        );
        if ($nPostings === 0) {
            return;
        }
        $ratio = (int)$this->in('Average answers per thread?', null, '10');
        $seed = $nPostings / $ratio;

        for ($i = 0; $i < $nPostings; $i++) {
            $newThread = $i < $seed;

            $posting = [
                'subject' => "$i",
                'text' => rand(0, 1) ? $this->_randomText() : '',
            ];
            if ($newThread) {
                $posting['category_id'] = $this->_randomCategory();
            } else {
                $posting['pid'] = array_rand($this->_Threads, 1);
            }
            $user = $this->_randomUser();

            $posting = $this->Entries->createPosting($posting, $user);

            if (empty($posting)) {
                throw new \RuntimeException(
                    'Could not create posting.'
                );
            }

            $this->_progress($i, $nPostings);

            $id = $posting->get('id');
            $this->_Threads[$id] = $id;
        }

        $this->out();
        $this->out("Generated $i postings.");
    }

    /**
     * generate users
     *
     * @return void
     */
    public function generateUsers()
    {
        $max = count($this->_users);
        $n = (int)$this->in(
            "Number of users to generate (max: $max)?",
            null,
            '0'
        );
        if ($n === 0) {
            return;
        }
        if ($n > $max) {
            $n = $max;
        }
        $users = array_rand($this->_users, $n);
        $i = 0;
        foreach ($users as $user) {
            $name = $this->_users[$user];
            $data = [
                'username' => $name,
                'password' => 'test',
                'password_confirm' => 'test',
                'user_email' => "$name@example.com"
            ];
            $this->Users->register($data, true);
            $this->_progress($i++, $n);
        }

        $this->out();
        $this->out("Generated $i users.");
    }

    /**
     * Update progress
     *
     * @param int $i current
     * @param int $off 100%
     * @return void
     */
    protected function _progress($i, $off)
    {
        if ($i < 1) {
            return;
        }
        $this->out('.', 0);
        if ($i > 1 && !($i % 50)) {
            $percent = (int)floor($i / $off * 100);
            $this->out(sprintf(' %3s%%', $percent), 1);
        }
    }

    /**
     * Return random category
     *
     * @return int category_id
     */
    protected function _randomCategory()
    {
        if ($this->_Categories === null) {
            $this->_Categories = $this->Entries->Categories->find(
                'all',
                ['fields' => ['id']]
            )->toArray();
        }
        $id = array_rand($this->_Categories, 1);

        return $this->_Categories[$id]->get('id');
    }

    /**
     * Return random user
     *
     * @return CurrentUserInterface a user
     */
    protected function _randomUser()
    {
        if ($this->_Users === null) {
            $this->_Users = $this->Users->find(
                'all',
                ['conditions' => ['activate_code' => 0]]
            )->toArray();
        }
        $id = array_rand($this->_Users, 1);

        $user = CurrentUserFactory::createDummy($this->_Users[$id]->toArray());
        $user->set('user_type', 'admin');

        return $user;
    }

    /**
     * Return random text
     *
     * @return string text
     */
    protected function _randomText()
    {
        if (empty($this->_text)) {
            $this->_text = file_get_contents(
                'http://loripsum.net/api/short/plaintext'
            );
        }

        return $this->_text;
    }
}
