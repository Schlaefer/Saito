<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Middleware;

use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Installer\Lib\InstallerState;

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
        $url = $request->getUri()->getPath();
        if (!Configure::read('Saito.installed')) {
            if (strpos($url, '.')) {
                // Don't serve anything except existing assets and installer routes.
                // Automatic browser favicon.ico request messes-up installer state.
                return new Response(['status' => 503]);
            }
            $request = $request
                ->withParam('plugin', 'Installer')
                ->withParam('controller', 'Install');

            return $next($request, $response);
        } elseif (strpos($url, 'install/finished')) {
            //// User has has removed installer token. Installer no longer available.
            InstallerState::reset();

            return (new Response())->withLocation(Router::url('/'));
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
