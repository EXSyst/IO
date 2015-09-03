<?php

namespace EXSyst\Component\IO\Tests;

use EXSyst\Component\IO\Source;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class SourceTest extends \PHPUnit_Framework_TestCase
{
    const MIN_BLOCK_BYTE_COUNT = 4096;
    const MIN_SPAN_BLOCK_BYTE_COUNT = 128;

    public function testConstants()
    {
        $this->assertEquals(self::MIN_BLOCK_BYTE_COUNT, Source::MIN_BLOCK_BYTE_COUNT);
        $this->assertEquals(self::MIN_SPAN_BLOCK_BYTE_COUNT, Source::MIN_SPAN_BLOCK_BYTE_COUNT);
    }

    public function testFromString()
    {
        $string = 'Foobar';

        // With default values
        $source = Source::fromString($string);
        $this->assertEquals('Foobar', $source->read(6));

        // With a custom start
        $source = Source::fromString($string, 2);
        $this->assertEquals('obar', $source->read(4));
        $this->assertTrue($source->isFullyConsumed());

        // With a custom end
        $source = Source::fromString($string, 1, 5);
        $this->assertEquals('oob', $source->read(3));
        $this->assertFalse($source->isFullyConsumed());
        $this->assertEquals('a', $source->read(1));
        $this->assertTrue($source->isFullyConsumed());
    }
}
