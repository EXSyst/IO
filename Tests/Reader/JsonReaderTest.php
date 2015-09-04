<?php

namespace EXSyst\Component\IO\Tests\Reader;

use EXSyst\Component\IO\Reader\CDataReader;
use EXSyst\Component\IO\Reader\JsonReader;
// use EXSyst\Component\IO\Source\StringSource;
use EXSyst\Component\IO\Tests\Source\OuterSourceTest;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class JsonReaderTest extends OuterSourceTest
{
    const INNER_SOURCE_CLASS = CDataReader::class;
    const OUTER_SOURCE_CLASS = JsonReader::class;

    public function testReadValue()
    {
        list($reader) = $this->createMockedSource(['readJsonValue']);

        $reader->expects($this->exactly(3))
            ->method('readJsonValue')
            ->withConsecutive(
                [512],
                [512]
            )
            ->willReturnOnConsecutiveCalls(
                '{"foo": "bar"}',
                '{"foo": "bar"}',
                'null'
            );

        $value = new \stdClass();
        $value->foo = 'bar';
        $this->assertEquals($value, $reader->readValue());
        $this->assertEquals(['foo' => 'bar'], $reader->readValue(true));
        $this->assertNull($reader->readValue());
    }

    /**
     * @expectedException EXSyst\Component\IO\Exception\EncodingException
     */
    public function testReadInvalidValue()
    {
        list($reader) = $this->createMockedSource(['readJsonValue']);

        $reader->expects($this->once())
            ->method('readJsonValue')
            ->willReturn('invalidJson');

        $reader->readValue();
    }
}
