<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace SaitoSearch\Test\Controller;

use Saito\Exception\SaitoForbiddenException;
use Saito\Test\IntegrationTestCase;

/*
class SearchesMockController extends SearchesController
{

    public function sanitize($string)
    {
        return $this->_sanitize($string);
    }
}
*/

/**
 * SearchesController Test Case
 *
 */
class SearchesControllerTest extends IntegrationTestCase
{

    /** @var array Fixtures */
    public $fixtures = [
        'app.Category',
        'app.Entry',
        'app.Setting',
        'app.User',
        'app.UserBlock',
        'app.UserIgnore',
        'app.UserRead',
        'app.UserOnline',
    ];

    /**
     * Sorting search results by rank
     */
    public function testSimpleSortByRank()
    {
        $this->skipOnDataSource('Postgres');
        $this->_loginUser(1);

        $this->get('/searches/simple?searchTerm="Second_Subject"&order=rank');

        $this->assertResponseCode(200);

        $result = $this->viewVariable('results');
        $this->assertEquals(2, $result->first()->get('id'));
        $this->assertEquals(5, $result->skip(1)->first()->get('id'));
    }

    /**
     * Admin Category results should be in search results for admin
     */
    public function testSimpleAccession()
    {
        $this->skipOnDataSource('Postgres');
        $this->_loginUser(1);

        $this->get('/searches/simple?searchTerm="Third+Thread+First_Subject"');
        $result = $this->viewVariable('results');

        $this->assertCount(1, $result);
    }

    /**
     * Admin Category results shouldn't be in search results for user
     */
    public function testSimpleNoAccession()
    {
        $this->skipOnDataSource('Postgres');
        $this->_loginUser(3);

        $this->get('/searches/simple?searchTerm="Third+Thread+First_Subject"');
        $result = $this->viewVariable('results');

        $this->assertCount(0, $result);
    }

    /**
     * Admin Category results should be in search results for admin
     */
    public function testAdvancedAccession()
    {
        $this->_loginUser(1);

        $this->get('/searches/advanced?subject=Third+Thread+First_Subject');
        $result = $this->viewVariable('results');

        $this->assertCount(1, $result);
    }

    /**
     * Admin Category results shouldn't be in search results for user
     */
    public function testAdvancedNoAccessionPassive()
    {
        $this->_loginUser(3);

        $this->get('/searches/advanced?subject=Third+Thread+First_Subject');
        $result = $this->viewVariable('results');

        $this->assertCount(0, $result);
    }

    public function testSearchAdvancedCategoryNoAccession()
    {
        $this->_loginUser(3);

        $this->expectException(SaitoForbiddenException::class);
        $this->get('/searches/advanced?subject=Third+Thread+First_Subject&category_id=1');
    }

    public function testSearchAdvancedUserPostings()
    {
        $this->_loginUser(1);

        $this->get('/searches/advanced?name=Alice');

        $results = $this->viewVariable('results');
        $this->assertNotEmpty($results);
    }
}
