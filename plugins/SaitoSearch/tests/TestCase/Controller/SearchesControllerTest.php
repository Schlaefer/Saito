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

use Cake\ORM\TableRegistry;
use DateTimeImmutable;
use Saito\Exception\SaitoForbiddenException;
use Saito\Test\IntegrationTestCase;

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
    public function testSearchSimpleSortByRank()
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
    public function testSearchSimpleAccession()
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
    public function testSearchSimpleNoAccession()
    {
        $this->_loginUser(3);

        $this->get('/searches/simple?searchTerm="Third+Thread+First_Subject"');
        $result = $this->viewVariable('results');

        $this->assertCount(0, $result);
    }

    /**
     * Admin Category results should be in search results for admin
     */
    public function testSearchAdvancedAccession()
    {
        $url = '/searches/advanced?subject=Third+Thread+First_Subject&year[year]=1999';

        /// No access for normal user
        $this->_loginUser(3);
        $this->get($url);
        $result = $this->viewVariable('results');
        $this->assertCount(0, $result);

        /// Access for admin
        $this->_loginUser(1);
        $this->get($url);
        $result = $this->viewVariable('results');
        $this->assertCount(1, $result);
    }

    public function testAdvancedSearchWithNoExistingPostings()
    {
        $this->_loginUser(3);

        $EntriesTable = TableRegistry::getTableLocator()->get('Entries');
        $EntriesTable->deleteAll('id > 0');

        $url = '/searches/advanced?subject=foo';
        $this->get($url);

        $this->assertResponseCode(200);
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

        $this->get('/searches/advanced?name=Alice&year[year]=1999');

        $results = $this->viewVariable('results');
        $this->assertNotEmpty($results);
    }

    /**
     * Limit default search range to the last year
     */
    public function testSearchAdvancedSinceLastYear()
    {
        $this->_loginUser(3);

        $this->get('/searches/advanced');

        $actualMonth = $this->viewVariable('month');
        $expectedMonth = (new DateTimeImmutable())->format('n');
        $this->assertEquals($expectedMonth, $actualMonth);
    }
}
