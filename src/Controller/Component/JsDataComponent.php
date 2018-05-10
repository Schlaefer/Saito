<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;
use Saito\JsData\JsData;

class JsDataComponent extends Component
{
    protected $_JsData;

    /**
     * {@inheritDoc}
     */
    public function startup(Event $event)
    {
        $this->_JsData = new JsData();
    }

    /**
     * CakePHP beforeRender event-handler
     *
     * @param Event $event event
     * @return void
     */
    public function beforeRender(Event $event)
    {
        $event->getSubject()->set('jsData', $this->_JsData);
    }

    /**
     * {@inheritDoc}
     */
    public function __call($method, $params)
    {
        $proxy = [$this->_JsData, $method];
        if (is_callable($proxy)) {
            return call_user_func_array($proxy, $params);
        }

        throw new \RuntimeException("Method JsData::$method does not exist.");
    }
}
