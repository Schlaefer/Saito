<?php

declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\Test\Posting;

use App\Model\Table\EntriesTable;
use Saito\Posting\Posting;
use Saito\Posting\TreeBuilder;
use Saito\Test\Model\Table\SaitoTableTestCase;

class PostingTest extends SaitoTableTestCase
{
    public $tableClass = 'Entries';

    /** @var EntriesTable */
    public $Table;

    public $fixtures = [
        'app.Entry',
        'app.Category',
        'app.User',
    ];

    public function testGetAllChildren()
    {
        $posting = $this->Table->postingsForThread(1);

        $expected = [2, 3, 7, 8, 9];
        $result = $posting->getAllChildren();
        $actual = array_keys($result);
        sort($actual);
        $this->assertEquals($actual, $expected);

        $expected = [3, 7, 9];
        $result = $posting->getThread()->get(2)->getAllChildren();
        $actual = array_keys($result);
        sort($actual);
        $this->assertEquals($actual, $expected);
    }
}
