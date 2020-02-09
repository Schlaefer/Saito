<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Test\Auth;

use App\Auth\LegacyPasswordHasherSaltless;
use Saito\Test\SaitoTestCase;

class LegacyPasswordHasherSaltlessTest extends SaitoTestCase
{

    public function setUp(): void
    {
        $this->Hasher = new LegacyPasswordHasherSaltless(['hashType' => 'md5']);
    }

    public function tearDown(): void
    {
        unset($this->Hasher);
    }

    public function testCheck()
    {
        $password = 'Rosinenbrötchen';
        $hash = 'df7d879155bec3f2674c2b3e03fe9086';
        $this->assertTrue($this->Hasher->check($password, $hash));

        // Test own hash
        $password = 'Rosinenbrötchen';
        $hash = $this->Hasher->hash($password);
        $this->assertTrue($this->Hasher->check($password, $hash));

        $this->assertFalse($this->Hasher->check((string)mt_rand(1, 99999), $hash));
    }
}
