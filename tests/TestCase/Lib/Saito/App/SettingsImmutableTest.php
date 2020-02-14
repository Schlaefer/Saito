<?php
declare(strict_types=1);

namespace Saito\Test\Lib\App;

use PHPUnit\Framework\TestCase;
use Saito\App\SettingsImmutable;

class SettingsImmutableTest extends TestCase
{
    public function testGet()
    {
        $settings = (new SettingsImmutable(['foo' => 'bar']));
        $actual = $settings->get('foo');
        $this->assertEquals('bar', $actual);
    }

    public function testGetNotSet()
    {
        $settings = (new SettingsImmutable([]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1524226492);

        $settings->get('foo');
    }
}
