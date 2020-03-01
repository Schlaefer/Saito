<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace BbcodeParser\Test\Lib;

use BbcodeParser\Lib\Markup;
use Saito\Markup\MarkupSettings;
use Saito\Test\SaitoTestCase;

class MarkupTest extends SaitoTestCase
{
    /**
     * @var Markup
     */
    private $markup;

    public function setUp(): void
    {
        parent::setUp();
        $this->markup = new Markup(new MarkupSettings(['quote_symbol' => '»']));
    }

    public function tearDown(): void
    {
        unset($this->markup);
        parent::tearDown();
    }

    public function testCiteEmptyText()
    {
        $input = '';
        $result = $this->markup->citeText($input);
        $expected = '';
        $this->assertEquals($result, $expected);
    }

    public function testCiteText()
    {
        $input = "123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789";
        $result = $this->markup->citeText($input);
        $expected = "» 123456789 123456789 123456789 123456789 123456789 123456789 123456789\n» 123456789\n";
        $this->assertEquals($result, $expected);
    }
}
