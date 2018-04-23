<?php

namespace SaitoSearch\Test;

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
