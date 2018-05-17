<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Installer\Middleware;

use Cake\Http\Response;
use Cake\Http\ServerRequest;

class InstallerMiddleware
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
        $request = $request
            ->withParam('plugin', 'Installer')
            ->withParam('controller', 'Install')
            ->withParam('action', 'start');

        $response = $next($request, $response);

        return $response;
    }
}
