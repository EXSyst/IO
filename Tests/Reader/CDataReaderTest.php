<?php

/*
 * This file is part of the IO package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Component\IO\Tests\Reader;

use EXSyst\Component\IO\Exception;
use EXSyst\Component\IO\Reader\CDataReader;
use EXSyst\Component\IO\Reader\StringCDataReader;
use EXSyst\Component\IO\Source\StringSource;
use EXSyst\Component\IO\Tests\Source\OuterSourceTest;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class CDataReaderTest extends OuterSourceTest
{
    const OUTER_SOURCE_CLASS = CDataReader::class;
    const WHITE_SPACE_MASK = "\011\n\013\014\r ";

    public function testConstant()
    {
        $this->assertEquals(CDataReader::WHITE_SPACE_MASK, self::WHITE_SPACE_MASK);
    }

    public function testCreationFromSource()
    {
        list($outerSource, $innerSource) = $this->createMockedSource(['eatSpan']);
        $stringSource = new StringSource('foo');

        $this->assertEquals($outerSource, CDataReader::fromSource($outerSource));
        $this->assertTrue(get_class(CDataReader::fromSource($innerSource)) == CDataReader::class);
        $this->assertTrue(get_class(CDataReader::fromSource($stringSource)) == StringCDataReader::class);
    }

    public function testCreationFromString()
    {
        $reader = CDataReader::fromString('foo', 4, 3);
        $source = $reader->getInnerSource();

        $this->assertInstanceOf(StringCDataReader::class, $reader);
        $this->assertEquals('foo', $source->data);
        $this->assertEquals(4, $source->offset);
        $this->assertEquals(3, $source->end);
    }

    public function testEat()
    {
        $source = new StringSource('Foo bar exsyst');
        list($reader) = $this->createMockedSource(null, $source);

        $this->assertTrue($reader->eat('Fo'));
        $this->assertFalse($reader->eat('bar'));
        $this->assertEquals('o ', $source->read(2));
        $this->assertTrue($reader->eat('bar exsyst'));
        $this->assertTrue($source->isFullyConsumed());
    }

    /**
     * @expectedException EXSyst\Component\IO\Exception\RuntimeException
     */
    public function testEatExceptionCatch()
    {
        list($reader, $source) = $this->createMockedSource();

        $source->expects($this->at(0))->method('peek')
            ->will($this->throwException(new Exception\UnderflowException()));

        $source->expects($this->at(1))->method('peek')
            ->will($this->throwException(new Exception\RuntimeException()));

        $this->assertFalse($reader->eat('foo'));
        $reader->eat('bar');
    }

    public function testEatCaseInsensitive()
    {
        $source = new StringSource('fOo bar eXSyst');
        list($reader) = $this->createMockedSource(null, $source);

        $this->assertEquals('fO', $reader->eatCaseInsensitive('FO'));
        $this->assertNull($reader->eatCaseInsensitive('baR'));
        $this->assertEquals('o ', $source->read(2));
        $this->assertEquals('bar eXSyst', $reader->eatCaseInsensitive('bar EXSYST'));
        $this->assertTrue($source->isFullyConsumed());
    }

    /**
     * @expectedException EXSyst\Component\IO\Exception\RuntimeException
     */
    public function testEatCaseInsensitiveExceptionCatch()
    {
        list($reader, $source) = $this->createMockedSource();

        $source->expects($this->at(0))->method('peek')
            ->will($this->throwException(new Exception\UnderflowException()));

        $source->expects($this->at(1))->method('peek')
            ->will($this->throwException(new Exception\RuntimeException()));

        $this->assertFalse($reader->eat('foo'));
        $reader->eat('bar');
    }

    public function testEatWhiteSpace()
    {
        list($reader) = $this->createMockedSource(['eatSpan']);

        $reader
            ->expects($this->exactly(2))
            ->method('eatSpan')
            ->withConsecutive(
                [CDataReader::WHITE_SPACE_MASK, $eatLength1 = 3, $allowIncomplete1 = true],
                [CDataReader::WHITE_SPACE_MASK, $eatLength2 = null, false]
            )
            ->will($this->onConsecutiveCalls(
                $return1 = "\011\013",
                $return2 = "  \r\n"
            ));

        $this->assertEquals(strlen($return1), $reader->eatWhiteSpace($eatLength1, $allowIncomplete1));
        $this->assertEquals(strlen($return2), $reader->eatWhiteSpace($eatLength2));
    }

    public function testEatToFullConsumption()
    {
        $source = new StringSource('foo foo exsyst bar');
        list($reader) = $this->createMockedSource(null, $source);

        $reader->read(4);

        $this->assertEquals('foo exsyst bar', $reader->eatToFullConsumption());
    }
}
