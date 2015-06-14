<?php

namespace Saito\Test;

use Saito\String\Properize;

class ProperizeTest extends SaitoTestCase
{

    public function testProperizeEng()
    {
        Properize::setLanguage('en_US');

        $input = 'Jack';
        $expected = 'Jack’s';
        $result = Properize::prop($input);
        $this->assertEquals($expected, $result);

        $input = 'James';
        $expected = 'James’';
        $result = Properize::prop($input);
        $this->assertEquals($expected, $result);

        $input = 'James™';
        $expected = 'James™’s';
        $result = Properize::prop($input);
        $this->assertEquals($expected, $result);

        $input = 'JAMES';
        $expected = 'JAMES’';
        $result = Properize::prop($input);
        $this->assertEquals($expected, $result);
    }

    public function testProperizeDeu()
    {
        Properize::setLanguage('de_DE');

        $input = 'Jack';
        $expected = 'Jacks';
        $result = Properize::prop($input);
        $this->assertEquals($expected, $result);

        $input = 'James';
        $expected = 'James’';
        $result = Properize::prop($input);
        $this->assertEquals($expected, $result);

        $input = 'James™';
        $expected = 'James™s';
        $result = Properize::prop($input);
        $this->assertEquals($expected, $result);

        $input = 'JAMES';
        $expected = 'JAMES’';
        $result = Properize::prop($input);
        $this->assertEquals($expected, $result);

        $this->assertEquals(Properize::prop('Bruce'), 'Bruce’');
        $this->assertEquals(Properize::prop('Weiß'), 'Weiß’');
        $this->assertEquals(Properize::prop('Merz'), 'Merz’');
    }
}
