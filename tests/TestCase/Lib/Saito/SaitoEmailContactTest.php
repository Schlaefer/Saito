<?php

namespace Saito\Test\Contact;

use Cake\Core\Configure;
use Saito\Contact\SaitoEmailContact;
use Saito\Test\SaitoTestCase;

/**
 * Class SaitoEmailContactTest
 *
 * @package Saito\Test\Contact
 * @group Saito\Test\Contact
 */
class SaitoEmailContactTest extends SaitoTestCase {

    public $fixtures = ['app.user'];

    public function testCakeFormat() {
        $input = ['foo' => 'bar'];
        $contact = new SaitoEmailContact($input);
        $this->assertEquals('bar', $contact->getName());
        $this->assertEquals('foo', $contact->getAddress());
        $this->assertEquals($input, $contact->toCake());
    }

    public function testBuildIn() {
        Configure::write('Saito.Settings.forum_name', 'bar');
        Configure::write('Saito.Settings.forum_email', 'foo');
        $input = 'main';
        $contact = new SaitoEmailContact($input);
        $this->assertEquals('bar', $contact->getName());
        $this->assertEquals('foo', $contact->getAddress());
    }

    public function testUser() {
        $input = 1;
        $contact = new SaitoEmailContact($input);
        $this->assertEquals('Alice', $contact->getName());
        $this->assertEquals('alice@example.com', $contact->getAddress());
    }

}
