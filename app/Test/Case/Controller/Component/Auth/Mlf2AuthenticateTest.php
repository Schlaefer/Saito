<?php

  App::uses('Mlf2Authenticate', 'Controller/Component/Auth');

  class Mlf2AuthenticateTest extends CakeTestCase {

    public function testPassword() {
      // test hash created on orignal mlf2 installation
      $password = 'Rosinenbrötchen';
      $hash = '203e5acdbd3bb71813e8abee44a5572313a227b75088133d47';
      $this->assertTrue(Mlf2Authenticate::checkPassword($password, $hash));

      // test own hash
      $password = 'Rosinenbrötchen';
      $hash = Mlf2Authenticate::hash($password);
      $this->assertTrue(Mlf2Authenticate::checkPassword($password, $hash));

      $this->assertFalse(Mlf2Authenticate::checkPassword(mt_rand(1, 99999), $hash));
    }

  }

?>