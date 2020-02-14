<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace SaitoSearch\Test\Lib;

use Saito\Test\SaitoTestCase;
use SaitoSearch\Lib\SimpleSearchString;

class SimpleSearchStringTest extends SaitoTestCase
{
    public function testReplaceOperators()
    {
        $in = '  zap  -foo-bar   baz  ';
        $S = new SimpleSearchString($in);

        $result = $S->replaceOperators();
        $expected = '+zap -"foo-bar" +baz';
        $this->assertEquals($expected, $result);

        $in = 'zap "foo-bar" baz';
        $S = new SimpleSearchString($in);
        $result = $S->replaceOperators();
        $expected = '+zap +"foo-bar" +baz';
        $this->assertEquals($expected, $result);
    }

    public function testValidateMinWordLength()
    {
        $in = '+test -fooo';
        $S = new SimpleSearchString($in);
        $result = $S->validateLength();
        $this->assertTrue($result);

        $in = '+tes -foo';
        $S = new SimpleSearchString($in);
        $result = $S->validateLength();
        $this->assertFalse($result);

        $in = 'test +foo';
        $S = new SimpleSearchString($in);
        $result = $S->validateLength();
        $this->assertFalse($result);

        $in = 'wm-foo';
        $S = new SimpleSearchString($in);
        $result = $S->validateLength();
        $this->assertTrue($result);

        $in = 'w-m';
        $S = new SimpleSearchString($in);
        $result = $S->validateLength();
        $this->assertFalse($result);

        $in = 'wmf';
        $S = new SimpleSearchString($in);
        $result = $S->validateLength();
        $this->assertFalse($result);

        $in = '';
        $S = new SimpleSearchString($in);
        $result = $S->validateLength();
        $this->assertFalse($result);

        $in = 'wm foo';
        $S = new SimpleSearchString($in);
        $result = $S->validateLength();
        $this->assertFalse($result);

        $in = '"wm foo';
        $S = new SimpleSearchString($in);
        $result = $S->validateLength();
        $this->assertFalse($result);

        $in = '"wm foo"';
        $S = new SimpleSearchString($in);
        $result = $S->validateLength();
        $this->assertTrue($result);
    }
}
