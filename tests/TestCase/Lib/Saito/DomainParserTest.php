<?php

namespace Saito\Test;

use PHPUnit_Framework_TestCase;
use Saito\DomainParser;

class DomainParserTest extends PHPUnit_Framework_TestCase
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
