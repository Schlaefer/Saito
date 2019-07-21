<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User\CurrentUser;

use Cake\Controller\Controller;
use Cake\ORM\TableRegistry;
use Saito\User\Cookie\Storage;
use Saito\User\CurrentUser\CurrentUser;
use Saito\User\CurrentUser\CurrentUserInterface;
use Saito\User\LastRefresh\LastRefreshCookie;
use Saito\User\LastRefresh\LastRefreshDatabase;
use Saito\User\LastRefresh\LastRefreshDummy;
use Saito\User\ReadPostings\ReadPostingsCookie;
use Saito\User\ReadPostings\ReadPostingsDatabase;
use Saito\User\ReadPostings\ReadPostingsDummy;

/**
 * Creates different current-user types
 */
class CurrentUserFactory
{
    /**
     * Creates a logged-in user
     *
     * @param array $config user configuration
     * @return CurrentUserInterface
     */
    public static function createLoggedIn(array $config = []): CurrentUserInterface
    {
        $CurrentUser = new CurrentUser($config);

        $CurrentUser->setLastRefresh(
            new LastRefreshDatabase(
                $CurrentUser,
                TableRegistry::getTableLocator()->get('Users')
            )
        );
        $CurrentUser->setReadPostings(
            new ReadPostingsDatabase(
                $CurrentUser,
                TableRegistry::getTableLocator()->get('UserReads'),
                TableRegistry::getTableLocator()->get('Entries')
            )
        );

        return $CurrentUser;
    }

    /**
     * Creates a visitor
     *
     * @param Controller $controller CakePHP controller access request/response
     * @param array|null $config user configuration
     * @return CurrentUserInterface
     */
    public static function createVisitor(Controller $controller, ?array $config = []): CurrentUserInterface
    {
        $config['user_type'] = 'anon';
        $CurrentUser = new CurrentUser($config);

        $storage = new Storage($controller, 'lastRefresh');
        $CurrentUser->setLastRefresh(new LastRefreshCookie($CurrentUser, $storage));
        $storage = new Storage($controller, 'Saito-Read');
        $CurrentUser->setReadPostings(new ReadPostingsCookie($CurrentUser, $storage));

        return $CurrentUser;
    }

    /**
     * Creates user without persistence (bots, testing)
     *
     * @param array|null $config user configuration (usually empty)
     * @return CurrentUserInterface
     */
    public static function createDummy(?array $config = []): CurrentUserInterface
    {
        $config['user_type'] = 'anon';
        $CurrentUser = new CurrentUser($config);

        $CurrentUser->setLastRefresh(new LastRefreshDummy(new CurrentUser([]), $CurrentUser));
        $CurrentUser->setReadPostings(new ReadPostingsDummy());

        return $CurrentUser;
    }
}
