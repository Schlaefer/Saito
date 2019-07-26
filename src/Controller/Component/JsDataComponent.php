<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

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
     * Add message
     *
     * @param string $message message
     * @param array|null $options options
     * @return void
     */
    public function addMessage(string $message, ?array $options = []): void
    {
        $this->_JsData->addMessage($message, $options);
    }
}
