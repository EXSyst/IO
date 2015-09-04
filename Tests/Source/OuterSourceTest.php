<?php

namespace EXSyst\Component\IO\Tests\Source;

use EXSyst\Component\IO\Reader\CDataReader;
use EXSyst\Component\IO\Source\BufferedSource;
use EXSyst\Component\IO\Source\OuterSource;
use EXSyst\Component\IO\Source\SourceInterface;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class OuterSourceTest extends \PHPUnit_Framework_TestCase
{
    const INNER_SOURCE_CLASS = SourceInterface::class;
    const OUTER_SOURCE_CLASS = OuterSource::class;
    const DEFAULT_CONSTRUCTOR_ARGS = null;

    /**
     * @var SourceInterface
     */
    protected $source;

    /**
     * @param array|null           $methods to mock
     * @param SourceInterface|null $source
     *
     * @return array
     */
    public function createMockedSource(array $methods = null, SourceInterface $source = null)
    {
        if (null === $source) {
            $source = $this->getMockBuilder(static::INNER_SOURCE_CLASS)
                ->disableOriginalConstructor()
                ->setMethods([])
                ->getMock();
        }

        return [
            $this->getMockBuilder(static::OUTER_SOURCE_CLASS)
                ->setConstructorArgs([$source])
                ->setMethods($methods)
                ->getMock(),
            $source,
        ];
    }

    public function testInheritance()
    {
        list($outerSource, $innerSource) = $this->createMockedSource();

        $this->assertInstanceOf(SourceInterface::class, $outerSource);
        $this->assertInstanceOf(OuterSource::class, $outerSource);
    }

    public function testConstructor()
    {
        list($outerSource, $innerSource) = $this->createMockedSource();

        $this->assertEquals($innerSource, \PHPUnit_Framework_Assert::readAttribute($outerSource, 'source'));
        $this->assertEquals($innerSource, $outerSource->getInnerSource());
    }

    public function testFixtureTypeSourceGetter()
    {
        $fixture = new FooSource();
        list($outerSource1) = $this->createMockedSource(null, $fixture);

        $outerSource2 = new BufferedSource($outerSource1);

        $this->assertEquals($fixture, $outerSource2->getSourceByType($fixture));
        $this->assertNull($outerSource2->getSourceByType('FooClass'));
    }

    public function testCDataReaderSourceGetter()
    {
        $source = $this->getMockForAbstractClass(FooSource::class);
        $reader = $this->getMockBuilder(CDataReader::class)
            ->setConstructorArgs([$source])
            ->getMock();
        list($outerSource, $innerSource) = $this->createMockedSource(null, $reader);

        $this->assertEquals($reader, $outerSource->getSourceByType(CDataReader::class));
        $this->assertEquals($source, $outerSource->getSourceByType(FooSource::class));
    }

    public function testInnermostSourceGetter()
    {
        list($outerSource1, $innerSource) = $this->createMockedSource();
        $outerSource2 = new BufferedSource($outerSource1);
        $outerSource3 = new BufferedSource($outerSource2);

        if ($innerSource instanceof OuterSource) {
            $innerSource = $innerSource->getInnerSource();
        }
        $this->assertEquals($innerSource, $outerSource3->getInnermostSource());
    }

    public function testConsumedByteCountGetter()
    {
        list($outerSource, $innerSource) = $this->createMockedSource();

        $innerSource
            ->expects($this->once())
            ->method('getConsumedByteCount')
            ->willReturn('foo');

        $this->assertEquals('foo', $outerSource->getConsumedByteCount());
    }

    public function testRemainingByteCountGetter()
    {
        list($outerSource, $innerSource) = $this->createMockedSource();

        $innerSource
            ->expects($this->once())
            ->method('getRemainingByteCount')
            ->willReturn('foo');

        $this->assertEquals('foo', $outerSource->getRemainingByteCount());
    }

    public function testFullyConsumedChecker()
    {
        list($outerSource, $innerSource) = $this->createMockedSource();

        $innerSource
            ->expects($this->once())
            ->method('isFullyConsumed')
            ->willReturn('foo');

        $this->assertEquals('foo', $outerSource->isFullyConsumed());
    }

    public function testWouldBlock()
    {
        list($outerSource, $innerSource) = $this->createMockedSource();

        $innerSource
            ->expects($this->exactly(2))
            ->method('wouldBlock')
            ->withConsecutive(
                [1, 2],
                ['bar', false]
            )
            ->willReturn('foo');

        $this->assertEquals('foo', $outerSource->wouldBlock(1, 2));
        $this->assertEquals('foo', $outerSource->wouldBlock('bar', false));
    }

    public function testBlockByteCountGetter()
    {
        list($outerSource, $innerSource) = $this->createMockedSource();

        $innerSource
            ->expects($this->once())
            ->method('getBlockByteCount')
            ->willReturn('foo');

        $this->assertEquals('foo', $outerSource->getBlockByteCount());
    }

    public function testBlockRemainingByteCountGetter()
    {
        list($outerSource, $innerSource) = $this->createMockedSource();

        $innerSource
            ->expects($this->once())
            ->method('getBlockRemainingByteCount')
            ->willReturn('foo');

        $this->assertEquals('foo', $outerSource->getBlockRemainingByteCount());
    }

    public function testCaptureState()
    {
        list($outerSource, $innerSource) = $this->createMockedSource();

        $innerSource
            ->expects($this->once())
            ->method('captureState')
            ->willReturn('foo');

        $this->assertEquals('foo', $outerSource->captureState());
    }

    public function testRead()
    {
        list($outerSource, $innerSource) = $this->createMockedSource();

        $innerSource
            ->expects($this->exactly(2))
            ->method('read')
            ->withConsecutive(
                ['bar', 'foobar'],
                ['foobar', false]
            )
            ->willReturn('foo');

        $this->assertEquals('foo', $outerSource->read('bar', 'foobar'));
        $this->assertEquals('foo', $outerSource->read('foobar'));
    }

    public function testPeek()
    {
        list($outerSource, $innerSource) = $this->createMockedSource();

        $innerSource
            ->expects($this->exactly(2))
            ->method('peek')
            ->withConsecutive(
                ['bar', 'foobar'],
                ['foobar', false]
            )
            ->willReturn('foo');

        $this->assertEquals('foo', $outerSource->peek('bar', 'foobar'));
        $this->assertEquals('foo', $outerSource->peek('foobar'));
    }

    public function testSkip()
    {
        list($outerSource, $innerSource) = $this->createMockedSource();

        $innerSource
            ->expects($this->exactly(2))
            ->method('skip')
            ->withConsecutive(
                ['bar', 'foobar'],
                ['foobar', false]
            )
            ->willReturn('foo');

        $this->assertEquals('foo', $outerSource->skip('bar', 'foobar'));
        $this->assertEquals('foo', $outerSource->skip('foobar'));
    }
}

class FooSource extends CDataReader
{
    public function __construct()
    {
    }
}
