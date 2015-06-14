<?php

namespace App\Routing\Filter;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Response;
use Cake\Routing\DispatcherFilter;

class SaitoResponseFilter extends DispatcherFilter
{
    /**
     * {@inheritDoc}
     */
    public function afterDispatch(Event $event)
    {
        $response = $event->data['response'];
        $this->_setXFrameOptionsHeader($response);
    }

    /**
     * Disallow iframe-embeding.
     *
     * @param Response $response Response
     * @return void
     */
    protected function _setXFrameOptionsHeader(Response $response)
    {
        $xFO = Configure::read('Saito.X-Frame-Options');
        if (empty($xFO)) {
            $xFO = 'SAMEORIGIN';
        }
        $response->header('X-Frame-Options', $xFO);
    }
}
