<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\Controller;

use Cake\Event\Event;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;

class StatusController extends AppController
{

    public $autoRender = false;

    /**
     * Sends status data to the frontend
     *
     * Even if no data is send for other functionality the ping keeps the
     * current user online.
     *
     * @return Response
     */
    public function status()
    {
        $data = [];
        $data = json_encode($data);
        if ($this->request->accepts('text/event-streams')) {
            $body = $this->_statusAsEventStream($data);
        } else {
            $body = $this->_statusAsJson($data);
        }
        $this->response = $this->response->withStringBody($body);

        return $this->response;
    }

    /**
     * Get status as event-stream
     *
     * @param string $data json-encoded data
     * @return string
     */
    protected function _statusAsEventStream($data)
    {
        // time in ms to next request
        $retry = '10000';
        $this->response = $this->response->withType(['eventstream' => 'text/event-stream']);
        $this->response = $this->response->withType('eventstream');
        $this->response->disableCache();
        $out = '';
        $out .= "retry: $retry\n";
        $out .= 'data: ' . $data . "\n\n";

        return $out;
    }

    /**
     * Get status as JSON response
     *
     * @param string $data json-encoded data
     * @return mixed
     */
    protected function _statusAsJson($data)
    {
        if ($this->request->is('ajax') === false) {
            throw new BadRequestException();
        }
        $this->response = $this->response->withType('json');

        return $data;
    }

    /**
     * {@inheritdoc}
     *
     * @param Event $event An Event instance
     * @return void
     */
    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->components()->unload('Authentication');
    }
}
