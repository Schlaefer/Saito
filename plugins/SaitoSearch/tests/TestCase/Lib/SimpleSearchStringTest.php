<?php

declare(strict_types = 1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2018
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace SaitoSearch\Test\Lib;

use SaitoSearch\Lib\SimpleSearchString;
use Saito\Test\SaitoTestCase;

class SimpleSearchStringTest extends SaitoTestCase
{

    public function testReplaceOperators()
    {
        $length = 4;
        $in = 'zap -foo-bar baz';
        $S = new SimpleSearchString($in, $length);

        $result = $S->replaceOperators();
        $expected = '+zap -"foo-bar" +baz';
        $this->assertEquals($expected, $result);

        $in = 'zap "foo-bar" baz';
        $S = new SimpleSearchString($in, $length);
        $result = $S->replaceOperators();
        $expected = '+zap +"foo-bar" +baz';
        $this->assertEquals($expected, $result);
    }

    public function testValidateMinWordLength()
    {
        $length = 4;
        $in = '+test -fooo';
        $S = new SimpleSearchString($in, $length);
        $result = $S->validateLength();
        $this->assertTrue($result);

        $in = '+tes -foo';
        $S = new SimpleSearchString($in, $length);
        $result = $S->validateLength();
        $this->assertFalse($result);

        $in = 'test +foo';
        $S = new SimpleSearchString($in, $length);
        $result = $S->validateLength();
        $this->assertFalse($result);

        $in = 'wm-foo';
        $S = new SimpleSearchString($in, $length);
        $result = $S->validateLength();
        $this->assertTrue($result);

        $in = 'w-m';
        $S = new SimpleSearchString($in, $length);
        $result = $S->validateLength();
        $this->assertFalse($result);

        $in = 'wmf';
        $S = new SimpleSearchString($in, $length);
        $result = $S->validateLength();
        $this->assertFalse($result);

        $in = '';
        $S = new SimpleSearchString($in, $length);
        $result = $S->validateLength();
        $this->assertFalse($result);

        $in = 'wm foo';
        $S = new SimpleSearchString($in, $length);
        $result = $S->validateLength();
        $this->assertFalse($result);

        $in = '"wm foo';
        $S = new SimpleSearchString($in, $length);
        $result = $S->validateLength();
        $this->assertFalse($result);

        $in = '"wm foo"';
        $S = new SimpleSearchString($in, $length);
        $result = $S->validateLength();
        $this->assertTrue($result);
    }
}
