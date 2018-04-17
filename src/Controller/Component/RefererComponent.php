<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Routing\Router;

class RefererComponent extends Component
{
    /**
     * Last request values
     *
     * @var array
     */
    private $last = ['action' => 'index', 'controller' => 'entries'];

    /**
     * {@inheritDoc}
     */
    public function beforeFilter(Event $event)
    {
        $baseUrl = Router::url('/', true);
        $referer = $event->getSubject()->referer();
        if (strpos($referer, $baseUrl) !== 0) {
            $this->last = [];

            return;
        }
        $referer = $event->getSubject()->referer(null, true);
        $parsed = Router::getRouteCollection()->parse($referer);
        foreach (['action', 'controller'] as $type) {
            if (isset($parsed[$type])) {
                $this->last[$type] = strtolower($parsed[$type]);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function beforeRender(Event $event)
    {
        $controller = $event->getSubject();
        $controller->set('referer', $this->last);
    }

    /**
     * Check if referer was controller $controller
     *
     * @param string $controller Controller to check for
     * @return bool
     */
    public function wasController(string $controller): bool
    {
        return !empty($this->last['controller']) && ($this->last['controller'] === $controller);
    }

    /**
     * Check if referer was action $action
     *
     * @param string $action Action to check for.
     * @return bool
     */
    public function wasAction(string $action): bool
    {
        return !empty($this->last['action']) && ($this->last['action'] === $action);
    }
}
