<?php

namespace App\Test\TestCase\Controller;

use Cake\Core\Configure;
use Saito\Test\IntegrationTestCase;
use Saito\User\Permission\ResourceAC;

class AppControllerTest extends IntegrationTestCase
{
    public $fixtures = [
        'app.Category',
        'app.Entry',
        'app.Setting',
        'app.Smiley',
        'app.SmileyCode',
        'app.User',
        'app.UserBlock',
        'app.UserIgnore',
        'app.UserOnline',
        'app.UserRead',
        'plugin.Bookmarks.Bookmark',
    ];

    /**
     * Test empty titleForLayout
     */
    public function testSetTitleForLayoutEmpty()
    {
        $this->get('/entries/index');
        $result = $this->viewVariable('titleForLayout');
        $this->assertEquals('Forum – macnemo', $result);
        $result = $this->viewVariable('titleForPage');
        $this->assertEquals('Forum', $result);
        $result = $this->viewVariable('forumName');
        $this->assertEquals('macnemo', $result);
    }

    /**
     * test nonempty titleForLayout
     */
    public function testSetTitleForLayoutNotEmpty()
    {
        $this->get('/entries/view/1');
        $result = $this->viewVariable('titleForLayout');
        $this->assertEquals('First_Subject | Ontopic – macnemo', $result);
    }

    /**
     * test empty title for layout with page_titles.po set
     */
    public function testSetTitleForLayoutPoFile()
    {
        $this->get('/users/register');
        $result = $this->viewVariable('titleForLayout');
        $this->assertEquals('Register – macnemo', $result);
    }

    public function testForumDisabledUser()
    {
        Configure::write('Saito.Settings.forum_disabled', true);

        $this->get('/');

        $text = Configure::read('Saito.Settings.forum_disabled_text');
        $this->assertResponseContains($text);
        $this->assertResponseCode(503);
    }

    public function testForumDisabledAdmin()
    {
        $this->_loginUser(1);
        Configure::write('Saito.Settings.forum_disabled', true);

        $this->get('/');

        $text = Configure::read('Saito.Settings.forum_disabled_text');
        $this->assertResponseNotContains($text);
        $this->assertResponseCode(200);
    }

    public function testForumDisabledLogin()
    {
        Configure::write('Saito.Settings.forum_disabled', true);

        $this->get('/login');

        $text = Configure::read('Saito.Settings.forum_disabled_text');
        $this->assertResponseNotContains($text);
        $this->assertResponseCode(200);
    }

    public function testRegisterLinkIsShown()
    {
        $this->setI18n('bzg');
        $this->get('/');
        $this->assertResponseContains('register_linkname');
    }

    public function testRegisterLinkNotShown()
    {
        $this->setI18n('bzg');
        Configure::read('Saito.Permission.Resources')
            ->get('saito.core.user.register')
            ->disallow((new ResourceAC())->asEverybody());
        $this->get('/');
        $this->assertResponseNotContains('register_linkname');
    }
}
