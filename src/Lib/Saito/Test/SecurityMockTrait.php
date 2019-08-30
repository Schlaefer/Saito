<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

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
