<?php

namespace Saito\Test;

use PHPUnit\Framework\TestCase;
use Saito\DomainParser;

class DomainParserTest extends TestCase
{

    public function testDomainAndTld()
    {
        $input = 'http://www.youtube.com/foo';
        $expected = 'youtube.com';
        $actual = DomainParser::domainAndTld($input);
        $this->assertEquals($expected, $actual);
    }

    public function testDomain()
    {
        $input = 'http://www.youtube.com/foo';
        $expected = 'youtube';
        $actual = DomainParser::domain($input);
        $this->assertEquals($expected, $actual);
    }
}
