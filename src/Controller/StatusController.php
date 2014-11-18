<?php

namespace App\Controller;

use Cake\Event\Event;
use Cake\Network\Exception\BadRequestException;
use Cake\ORM\TableRegistry;

class StatusController extends AppController
{

    public $autoRender = false;

    /**
     * Current app status ping
     *
     * @return string
     * @throws BadRequestException
     */
    public function status()
    {
        $Shouts = TableRegistry::get('Shouts');
        $data = [
            'lastShoutId' => $Shouts->findLastId()
        ];
        $data = json_encode($data);
        if ($this->request->accepts('text/event-streams')) {
            $body = $this->_statusAsEventStream($data);
        } else {
            $body = $this->_statusAsJson($data);
        }
        $this->response->body($body);

        return $this->response;
    }

    protected function _statusAsEventStream($data)
    {
        // time in ms to next request
        $_retry = '10000';
        $this->response->type(['eventstream' => 'text/event-stream']);
        $this->response->type('eventstream');
        $this->response->disableCache();
        $_out = '';
        $_out .= "retry: $_retry\n";
        $_out .= 'data: ' . $data . "\n\n";

        return $_out;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function _statusAsJson($data)
    {
        if ($this->request->is('ajax') === false) {
            throw new BadRequestException();
        }
        $this->response->type('json');

        return $data;
    }

    public function beforeFilter(Event $event)
    {
        parent::beforeFilter($event);
        $this->components()->unload('Auth');
    }

}
