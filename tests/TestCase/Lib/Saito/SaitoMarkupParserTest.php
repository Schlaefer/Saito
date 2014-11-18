<?php

namespace Saito\Test;

use Cake\View\Helper;
use Cake\View\View;
use Saito\Markup\Parser;

class ParserMock extends Parser
{

    public function parse($string, array $options = [])
    {
    }

}

class SaitoMarkupParserTest extends SaitoTestCase
{

    public function testCiteEmptyText()
    {
        $input = '';
        $result = $this->Parser->citeText($input);
        $expected = '';
        $this->assertEquals($result, $expected);
    }

    public function testCiteText()
    {
        $input = "123456789 123456789 123456789 123456789 123456789 123456789 123456789 123456789";
        $result = $this->Parser->citeText($input);
        $expected = "» 123456789 123456789 123456789 123456789 123456789 123456789 123456789\n» 123456789\n";
        $this->assertEquals($result, $expected);
    }

    public function setUp()
    {
        $View = new View();
        $Helper = new Helper($View);
        $this->Parser = new ParserMock($Helper, ['quote_symbol' => '»']);
    }

    public function tearDown()
    {
        unset($this->Parser);
    }

}
