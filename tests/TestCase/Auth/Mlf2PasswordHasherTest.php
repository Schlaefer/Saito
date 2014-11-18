<?php

namespace Saito\Test\Auth;

use App\Auth\Mlf2PasswordHasher;
use Saito\Test\SaitoTestCase;

class Mlf2PasswordHasherTest extends SaitoTestCase
{

    public function setUp()
    {
        $this->Hasher = new Mlf2PasswordHasher();
    }

    public function tearDown()
    {
        unset($this->Hasher);
    }

    public function testPassword()
    {
        // test hash created on orignal mlf2 installation
        $password = 'Rosinenbrötchen';
        $hash = '203e5acdbd3bb71813e8abee44a5572313a227b75088133d47';
        $this->assertTrue($this->Hasher->check($password, $hash));

        // test own hash
        $password = 'Rosinenbrötchen';
        $hash = $this->Hasher->hash($password);
        $this->assertTrue($this->Hasher->check($password, $hash));

        $this->assertFalse($this->Hasher->check(mt_rand(1, 99999), $hash));
    }

}
