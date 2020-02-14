<?php
declare(strict_types=1);

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
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Installer\Lib\InstallerState;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Loads Settings from DB into Configure
 */
class SaitoBootstrapMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /// start installer
        $url = $request->getUri()->getPath();
        if (!Configure::read('Saito.installed')) {
            if (strpos($url, '.')) {
                // Don't serve anything except existing assets and installer routes.
                // Automatic browser favicon.ico request messes-up installer state.
                return new Response(['status' => 503]);
            }
            $request = $this->forceRediret($request, 'Installer', 'controller');

            return $handler->handle($request);
        } elseif (strpos($url, 'install/finished')) {
            /// User has has removed installer token. Installer no longer available.
            InstallerState::reset();

            return (new Response())->withLocation(Router::url('/'));
        }

        /// load settings
        $tableLocator = TableRegistry::getTableLocator();
        /** @var \App\Model\Table\SettingsTable $settingsTable */
        $settingsTable = $tableLocator->get('Settings');
        $settingsTable->load(Configure::read('Saito.Settings'));

        /// start updater
        $updated = Configure::read('Saito.updated');
        if (!$updated) {
            $dbVersion = Configure::read('Saito.Settings.db_version');
            $saitoVersion = Configure::read('Saito.v');
            if ($dbVersion !== $saitoVersion) {
                $request = $this->forceRediret($request, 'Installer', 'Updater', 'start');
            }
        }

        return $handler->handle($request);
    }

    /**
     * Forces a particular Cake route on the request
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param null|string $plugin The plugin.
     * @param null|string $controller The controller.
     * @param null|string $action The action.
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected function forceRediret(
        ServerRequestInterface $request,
        ?string $plugin = null,
        ?string $controller = null,
        ?string $action = null
    ): ServerRequestInterface {
        $params = $request->getAttribute('params', []);
        foreach (['plugin', 'controller', 'action'] as $param) {
            if ($$param !== null) {
                $params[$param] = $$param;
            }
        }

        return $request->withAttribute('params', $params);
    }
}
