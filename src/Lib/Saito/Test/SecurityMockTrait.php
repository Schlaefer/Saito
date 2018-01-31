<?php

namespace Saito\Test;

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
        $this->enableSecurityToken();
    }

    /**
     * Disable CSRF protection.
     *
     * @return void
     */
    public function disableCsrf()
    {
        $this->enableCsrfToken();
    }
}
