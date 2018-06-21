<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Middleware;

use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;

/**
 * Loads Settings from DB into Configure
 */
class SaitoBootstrapMiddleware
{
    /**
     * Implements CakePHP 3 middleware
     *
     * @param ServerRequest $request request
     * @param Response $response response
     * @param callable $next next callable in middleware queue
     * @return Response
     */
    public function __invoke(ServerRequest $request, Response $response, $next): Response
    {
        //// start installer
        if (!Configure::read('Saito.installed')) {
            $request = $request
                ->withParam('plugin', 'Installer')
                ->withParam('controller', 'Install')
                ->withParam('action', 'start');

            return $next($request, $response);
        }

        //// load settings
        $tableLocator = TableRegistry::getTableLocator();
        /** @var SettingsTable $settingsTable */
        $settingsTable = $tableLocator->get('Settings');
        $settingsTable->load(Configure::read('Saito.Settings'));

        //// start updater
        $updated = Configure::read('Saito.updated');
        if (!$updated) {
            $dbVersion = Configure::read('Saito.Settings.db_version');
            $saitoVersion = Configure::read('Saito.v');
            if ($dbVersion !== $saitoVersion) {
                $request = $request
                    ->withParam('plugin', 'Installer')
                    ->withParam('controller', 'Updater')
                    ->withParam('action', 'start');
            }
        }

        return $next($request, $response);
    }
}
