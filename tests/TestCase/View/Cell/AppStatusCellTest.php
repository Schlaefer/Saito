<?php

namespace App\Test\TestCase\View\Cell;

use App\View\Cell\AppStatusCell;
use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Saito\Test\SaitoTestCase;

/**
 * App\View\Cell\AppStatusCell Test Case
 */
class AppStatusCellTest extends SaitoTestCase
{

    public $fixtures = [
        'app.category',
        'app.entry',
        'app.useronline',
        'app.user'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->request = $this->getMock('Cake\Network\Request');
        $this->response = $this->getMock('Cake\Network\Response');

        $this->AppStatus = new AppStatusCell($this->request, $this->response);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->AppStatus);

        parent::tearDown();
    }

    /**
     * Test display method
     *
     * @return void
     */
    public function testDisplay()
    {
    }
}
