<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Installer\Lib;

use App\Test\Fixture\UserFixture;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Saito\Test\IntegrationTestCase as SaitoIntegrationTestCase;

/**
 * Integration Test Case for Installer
 */
abstract class IntegrationTestCase extends SaitoIntegrationTestCase
{
    /**
     * Drop all known Saito tables (Saito 4 and 5)
     *
     * @return void
     */
    protected function dropTables()
    {
        $connection = ConnectionManager::get('test');
        $tables = [
            'bookmarks',
            'categories',
            'drafts',
            'entries',
            'esevents',
            'esnotifications',
            'phinxlog',
            'settings',
            'smiley_codes',
            'smilies',
            'uploads',
            'user_blocks',
            'user_ignores',
            'user_reads',
            'useronline',
            'users',
            'shouts',
        ];
        foreach ($tables as $table) {
            $connection->execute('DROP TABLE IF EXISTS ' . $table . ';');
        }

        // Tell the fixture manager that all tables are gone.
        // Usually tables are only emptied (TRUNCATE) but not deleted (DROP) between tests.
        $this->fixtureManager->shutDown();
    }
}
