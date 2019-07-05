<?php

namespace Saito\Test;

use Saito\DomainParser;

class DomainParserTest extends SaitoTestCase
{

    public function testDomainAndTld()
    {
        $input = 'http://www.youtöbe.com/foo';
        $expected = 'youtöbe.com';
        $actual = DomainParser::domainAndTld($input);
        $this->assertEquals($expected, $actual);
    }

    public function testDomain()
    {
        $input = 'http://www.youtub🐙e.com/foo';
        $expected = 'youtub🐙e';
        $actual = DomainParser::domain($input);
        $this->assertEquals($expected, $actual);
    }
}
