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

use Saito\Test\SaitoTestCase;
use Saito\User\Permission\Allowance\Role as Allowance;

class AllowanceTest extends SaitoTestCase
{
    public function testCheckSingle()
    {
        $allowance = new Allowance('resource', 'subject', 'object');

        $this->assertTrue($allowance->check('resource', 'subject', 'object'));
        $this->assertFalse($allowance->check('resource', 'subject'));

        $this->assertFalse($allowance->check('resource', 'foo', 'object'));

        $this->assertFalse($allowance->check('resource', 'subject', 'foo'));

        $this->assertFalse($allowance->check('foo', 'subject'));
        $this->assertFalse($allowance->check('foo', 'subject', 'object'));
    }

    public function testCheckMulti()
    {
        $allowance = new Allowance('resource', ['subject1', 'subject2'], 'object');

        $this->assertTrue($allowance->check('resource', 'subject1', 'object'));
        $this->assertTrue($allowance->check('resource', 'subject2', 'object'));

        $this->assertFalse($allowance->check('resource', 'foo', 'object'));

        $this->assertFalse($allowance->check('resource', 'subject', 'foo'));

        $this->assertFalse($allowance->check('foo', 'subject1'));
        $this->assertFalse($allowance->check('foo', 'subject2', 'object'));
    }

    public function testCheckSubjectIsAllowedOnAllObjects()
    {
        $allowance = new Allowance('resource', 'subject');

        $this->assertTrue($allowance->check('resource', 'subject'));
        $this->assertTrue($allowance->check('resource', 'subject', 'foo'));
    }

    public function testCheckMultipleObjects()
    {
        $allowance = new Allowance('resource', 'subject', ['object1', 'object2']);

        $this->assertTrue($allowance->check('resource', 'subject', 'object1'));
        $this->assertTrue($allowance->check('resource', 'subject', 'object2'));

        $this->assertFalse($allowance->check('resource', 'subject', 'object3'));
        $this->assertFalse($allowance->check('resource', 'subject'));
    }
}
