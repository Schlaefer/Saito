<?php

//@codingStandardsIgnoreStart
namespace App\Shell;

use App\Model\Table\EntriesTable;
use App\Model\Table\UsersTable;
use Cake\Console\Shell;
use Cake\Routing\Router;
use Saito\App\Registry;
use Saito\Markup\Settings;
use Saito\User\Auth;
use Saito\User\Categories;
use Saito\User\SaitoUser;

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

    protected $_Users = null;

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

    public function initialize()
    {
        parent::initialize();
        Registry::initialize();
        $this->loadModel('Entries');
        $this->loadModel('Users');
    }

    public function main()
    {
        $this->generateUsers();
        $this->generatePostings();
    }

    public function generatePostings()
    {
        $nPostings = (int)$this->in(
            'Number of postings to generate?',
            null,
            100
        );
        if ($nPostings === 0) {
            return;
        }
        $ratio = (int)$this->in('Average answers per thread?', null, 10);
        $seed = $nPostings / $ratio;

        $CurrentUser = new SaitoUserDummy();
        $CurrentUser->Categories = new Categories($CurrentUser);
        Registry::set('CU', $CurrentUser);

        for ($i = 0; $i < $nPostings; $i++) {
            $newThread = $i < $seed;

            $CurrentUser->setSettings($this->_randomUser());

            $posting = [
                'subject' => "$i",
                'text' => rand(0, 1) ? $this->_randomText() : '',
            ];
            if ($newThread) {
                $posting['category_id'] = $this->_randomCategory();
            } else {
                $posting['pid'] = array_rand($this->_Threads, 1);
            }
            $posting = $this->Entries->createPosting($posting);
            if (empty($posting)) {
                throw new \RuntimeException(
                    'Could not create entry: ' . $posting
                );
            }

            $this->_progress($i, $nPostings);

            $id = $posting->get('id');
            $this->_Threads[$id] = $id;
        }

        $this->out();
        $this->out("Generated $i postings.");
    }

    public function generateUsers()
    {
        $max = count($this->_users);
        $n = (int)$this->in(
            "Number of users to generate (max: $max)?",
            null,
            0
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

    protected function _randomUser()
    {
        if ($this->_Users === null) {
            $this->_Users = $this->Users->find(
                'all',
                ['conditions' => ['activate_code' => 0]]
            )->toArray();
        }
        $id = array_rand($this->_Users, 1);

        return $this->_Users[$id];
    }

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

class SaitoUserDummy extends SaitoUser
{

    public function isLoggedIn()
    {
        return true;
    }

    public function getRole()
    {
        return 'admin';
    }

    public function hasBookmarked()
    {
        return false;
    }

}
//@codingStandardsIgnoreStart
