<?php

namespace Saito\Test;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Event\EventManager;

trait SecurityMockTrait
{

    /**
     * Mock security component.
     *
     * @return void
     */
    public function mockSecurity()
    {
        $this->disableCsrf();
        EventManager::instance()->on('Controller.initialize', function (Event $event) {
            $Controller = $event->subject();
            $Security = $this->getMock(
                '\Cake\Controller\Component\SecurityComponent',
                ['_validatePost'],
                [$Controller->components()]
            );
            $Security
                ->expects($this->any())
                ->method('_validatePost')
                ->will($this->returnValue(true));
            $Controller->components()->set('Security', $Security);
        });
    }

    /**
     * Disable CSRF protection.
     *
     * @return void
     */
    public function disableCsrf()
    {
        EventManager::instance()->on('Controller.initialize', function (Event $event) {
            $Controller = $event->subject();
            $Controller->components()->unload('Csrf');
        });
    }
}
