<?php

use Saito\Test\SaitoTestCase;
use Saito\User\CurrentUser\CurrentUserFactory;

/**
 * Class CategoriesTest
 *
 * @group Saito\Test\User\CategoriesTest
 */
class CategoriesTest extends SaitoTestCase
{
    public $fixtures = ['app.Category', 'app.User'];

    public function setUp()
    {
        parent::setUp();
        $this->insertCategoryPermissions();
    }

    public function testGetAllForAnon()
    {
        $User = CurrentUserFactory::createDummy(['id' => 1, 'user_type' => 'anon']);
        $Lib = $User->getCategories();

        /**
         * test read
         */
        $result = $Lib->getAll('read', 'select');
        $expected = [
            3 => 'Another Ontopic',
            2 => 'Ontopic'
        ];
        $this->assertEquals($result, $expected);

        $result = $Lib->getAll('read');
        $expected = [
            3 => 3,
            2 => 2
        ];
        $this->assertEquals($result, $expected);

        /**
         * test new
         */
        $result = $Lib->getAll('thread');
        $this->assertEquals([], $result);
    }

    public function testGetAllFormatList()
    {
        $User = CurrentUserFactory::createDummy(['id' => 1, 'user_type' => 'anon']);
        $Lib = $User->getCategories();

        $result = $Lib->getAll('read', 'list');
        $expected = [
            ['id' => 3, 'title' => 'Another Ontopic'],
            ['id' => 2, 'title' => 'Ontopic'],
        ];
        $this->assertEquals($result, $expected);
    }

    public function testGetAllForUser()
    {
        $User = CurrentUserFactory::createLoggedIn(['id' => 1, 'user_type' => 'user']);
        $Lib = $User->getCategories();

        $result = $Lib->getAll('read', 'select');
        $expected = [
            3 => 'Another Ontopic',
            2 => 'Ontopic',
            4 => 'Offtopic',
        ];
        $this->assertEquals($result, $expected);

        /**
         * test new
         */
        $result = $Lib->getAll('thread', 'select');
        $expected = [
            3 => 'Another Ontopic',
            2 => 'Ontopic',
        ];
        $this->assertEquals($expected, $result);

        /**
         * test answer
         */
        $result = $Lib->getAll('answer', 'select');
        $expected = [
            3 => 'Another Ontopic',
            2 => 'Ontopic',
            4 => 'Offtopic'
        ];
        $this->assertEquals($expected, $result);
    }

    public function testGetAllForMod()
    {
        $User = CurrentUserFactory::createLoggedIn(['id' => 1, 'user_type' => 'mod']);
        $Lib = $User->getCategories();

        $expected = [
            1 => 'Admin',
            3 => 'Another Ontopic',
            2 => 'Ontopic',
            4 => 'Offtopic',
            5 => 'Trash',
        ];
        $result = $Lib->getAll('read', 'select');
        $this->assertEquals($result, $expected);

        /**
         * test answer as
         */
        $result = $Lib->getAll('answer', 'select');
        $this->assertEquals($expected, $result);

        /**
         * test new
         */
        unset($expected[5]);
        $result = $Lib->getAll('thread', 'select');
        $this->assertEquals($expected, $result);
    }

    public function testGetCustomNotSet()
    {
        $User = CurrentUserFactory::createLoggedIn(['id' => 3, 'user_type' => 'user']);
        $Lib = $User->getCategories();

        $expected = [2, 3, 4];
        $expected = array_combine($expected, $expected);
        $this->assertEquals($expected, $Lib->getCustom('read'));
    }
}
