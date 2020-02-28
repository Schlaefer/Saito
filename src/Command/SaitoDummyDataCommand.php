<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Saito\App\Registry;
use Saito\RememberTrait;
use Saito\User\CurrentUser\CurrentUserFactory;

/**
 * Creates dummy data for development
 *
 * @property \App\Model\Table\EntriesTable $Entries
 * @property \App\Model\Table\UsersTable $Users
 */
class SaitoDummyDataCommand extends Command
{
    use RememberTrait;

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
        'Walt',
    ];

    /**
     * {@inheritDoc}
     */
    public function initialize(): void
    {
        parent::initialize();
        Registry::initialize();
        $this->loadModel('Entries');
        $this->loadModel('Users');
    }

    /**
     * {@inheritDoc}
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->out('Hello world.');
        $this->generateUsers($io);
        $this->generatePostings($io);
    }

    /**
     * Generate postings
     *
     * @param \Cake\Console\ConsoleIo $io I/O
     * @return void
     */
    public function generatePostings(ConsoleIo $io)
    {
        $nPostings = (int)$io->ask('Number of postings to generate?', '100');
        if ($nPostings === 0) {
            return;
        }
        $ratio = (int)$io->ask('Average answers per thread?', '10');
        $seed = $nPostings / $ratio;

        for ($i = 0; $i < $nPostings; $i++) {
            $newThread = $i < $seed;

            $user = $this->_randomUser();
            $posting = [
                'name' => $user->get('username'),
                'subject' => "$i",
                'text' => rand(0, 1) ? $this->_randomText() : '',
                'user_id' => $user->getId(),
            ];
            if ($newThread) {
                $posting['pid'] = 0;
                $posting['category_id'] = $this->_randomCategory();
            } else {
                $id = array_rand($this->_Threads, 1);
                $posting['category_id'] = $this->_Threads[$id]['category_id'];
                $posting['tid'] = $this->_Threads[$id]['tid'];
                $posting['pid'] = $this->_Threads[$id]['id'];
            }

            $posting = $this->Entries->createEntry($posting);
            if ($posting->hasErrors()) {
                var_dump($posting->getErrors());
            }

            if (empty($posting)) {
                throw new \RuntimeException(
                    'Could not create posting.'
                );
            }

            $this->_progress($io, $i, $nPostings);

            $id = $posting->get('id');
            $this->_Threads[] = [
                'category_id' => $posting->get('category_id'),
                'id' => $id,
                'tid' => $posting->get('tid'),
            ];
        }

        $io->out('');
        $io->out("Generated $i postings.");
    }

    /**
     * generate users
     *
     * @param \Cake\Console\ConsoleIo $io I/O
     * @return void
     */
    public function generateUsers(ConsoleIo $io)
    {
        $max = count($this->_users);
        $n = (int)$io->ask("Number of users to generate (max: $max)?", '0');
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
                'user_email' => "$name@example.com",
            ];
            $this->Users->register($data, true);
            $this->_progress($io, $i++, $n);
        }

        $io->out('');
        $io->out("Generated $i users.");
    }

    /**
     * Update progress
     *
     * @param \Cake\Console\ConsoleIo $io I/O
     * @param int $i current
     * @param int $off 100%
     * @return void
     */
    protected function _progress($io, $i, $off)
    {
        if ($i < 1) {
            return;
        }
        $io->out('.', 0);
        $line = $i % 50;
        if ($i > 1 && !$line) {
            $percent = (int)floor($i / $off * 100);
            $io->out(sprintf(' %3s%%', $percent), 1);
        }
    }

    /**
     * Return random category
     *
     * @return int category_id
     */
    protected function _randomCategory()
    {
        $categories = $this->remember('existingCategories', function (): array {
            return $this->Entries->Categories->find(
                'all',
                ['fields' => ['id']]
            )->toArray();
        });
        $id = array_rand($categories, 1);

        return $categories[$id]->get('id');
    }

    /**
     * Return random user
     *
     * @return \Saito\User\CurrentUser\CurrentUserInterface a user
     */
    protected function _randomUser()
    {
        $users = $this->remember('existingUsers', function (): array {
            return $this->Users->find(
                'all',
                ['conditions' => ['activate_code' => 0]]
            )->toArray();
        });
        $id = array_rand($users, 1);

        $user = CurrentUserFactory::createDummy($users[$id]->toArray());
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
