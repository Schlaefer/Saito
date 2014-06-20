<?php

	App::uses('DomainParser', 'Lib');

	class DomainParserTest extends PHPUnit_Framework_TestCase {

		public function testDomainAndTld() {
			$in = 'http://www.youtube.com/foo';
			$expected = 'youtube.com';
			$actual = DomainParser::domainAndTld($in);
			$this->assertEquals($expected, $actual);
		}

		public function testDomain() {
			$in = 'http://www.youtube.com/foo';
			$expected = 'youtube';
			$actual = DomainParser::domain($in);
			$this->assertEquals($expected, $actual);
		}

	}
