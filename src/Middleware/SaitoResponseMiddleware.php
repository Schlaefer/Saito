<?php

namespace App\Middleware;

use Cake\Http\ServerRequest;
use Cake\Network\Response;

class SaitoResponseMiddleware
{

    /**
     * Configuration
     *
     * @var array
     */
    private $config = [
        'xFrameOptions' => 'SAMEORIGIN'
    ];

    /**
     * Constructor
     *
     * @param array $config Configuration for this middleware
     *  - `xFrameOptions` string xframe option
     * @return void
     */
    public function construct(array $config = [])
    {
        $this->$config = $config + $this->config;
    }

    /**
     * Middleware invoker
     *
     * @param ServerRequest $request ServerRequest
     * @param Response $response Response
     * @param callable $next next handler in Middleware queue
     * @return Response
     */
    public function __invoke(ServerRequest $request, Response $response, callable $next): Response
    {
        $response = $next($request, $response);
        $response = $this->_setXFrameOptionsHeader($response);

        return $response;
    }

    /**
     * Disallow iframe-embeding.
     *
     * @param Response $response Response
     * @return void
     */
    protected function _setXFrameOptionsHeader(Response $response)
    {
        $xFO = $this->config['xFrameOptions'] ?? false;
        if (!$xFO) {
            return;
        }

        return $response->withHeader('X-Frame-Options', $xFO);
    }
}
