<?php

namespace EXSyst\Component\IO\Tests;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class TeeSinkTest extends AbstractSinkTest
{
    public function setUp()
    {
        $this->sinkBuilder = $this->getMockBuilder('EXSyst\Component\IO\Sink\TeeSink')
            ->setConstructorArgs([array()])
            ->setMethods(null);
    }

    /**
     * @expectedException EXSyst\Component\IO\Exception\InvalidArgumentException
     * @expectedExceptionMessage The sub-sinks must be instanceof EXSyst\Component\IO\Sink\SinkInterface
     */
    public function testCreationWithAnInvalidSink()
    {
        $this->sinkBuilder
            ->setConstructorArgs([array(
                $this->createMockedSink(),
                new \stdClass(),
            )])
            ->getMock();
    }

    public function testDefaultValues()
    {
        $mockedSink1 = $this->createMockedSink();
        $mockedSink2 = $this->createMockedSink();

        $sink = $this->sinkBuilder
            ->setConstructorArgs([array('s1' => $mockedSink1, 'foo' => $mockedSink2)])
            ->getMock();

        $this->assertEquals([$mockedSink1, $mockedSink2], \PHPUnit_Framework_Assert::readAttribute($sink, 'sinks'));
        $this->assertEquals([$mockedSink1, $mockedSink2], $sink->getSinks());
        $this->assertEquals(0, \PHPUnit_Framework_Assert::readAttribute($sink, 'written'));

        $sink = $this->sinkBuilder->setConstructorArgs(array([]))->getMock();
        $this->assertEquals(1, $sink->getBlockByteCount());
    }

    public function testBlockSizeGetter()
    {
        $mockedSink1 = $this->createMockedSink();
        $mockedSink1
            ->expects($this->once())
            ->method('getBlockByteCount')
            ->willReturn(13403);

        $mockedSink2 = $this->createMockedSink();
        $mockedSink2
            ->expects($this->once())
            ->method('getBlockByteCount')
            ->willReturn(4234);

        $mockedSink3 = $this->createMockedSink();
        $mockedSink3
            ->expects($this->once())
            ->method('getBlockByteCount')
            ->willReturn(3240);

        $sink = $this->sinkBuilder
            ->setConstructorArgs([array($mockedSink1, $mockedSink2, $mockedSink3)])
            ->getMock();

        $this->assertEquals(91932249240, $sink->getBlockByteCount());
    }

    public function testBlockRemainingBytesCountGetter()
    {
        $firstWrite = $this->getRandomData(200);
        $secondWrite = $this->getRandomData(57);

        $mockedSink1 = $this->createMockedSink();
        $mockedSink1
            ->expects($this->exactly(2))
            ->method('getBlockByteCount')
            ->willReturn(324);
        $mockedSink1
            ->expects($this->exactly(2))
            ->method('write')
            ->withConsecutive(
                array($firstWrite),
                array($secondWrite)
            );

        $mockedSink2 = $this->createMockedSink();
        $mockedSink2
            ->expects($this->exactly(2))
            ->method('getBlockByteCount')
            ->willReturn(568);
        $mockedSink2
            ->expects($this->exactly(2))
            ->method('write')
            ->withConsecutive(
                array($firstWrite),
                array($secondWrite)
            );

        $sink = $this->sinkBuilder
            ->setConstructorArgs(array([$mockedSink1, $mockedSink2]))
            ->getMock();

        $blockByteCount = 46008;
        $this->assertEquals($blockByteCount, $sink->getBlockByteCount());

        $sink->write($firstWrite);
        $sink->write($secondWrite);

        $this->assertEquals(
            ($blockByteCount - strlen($firstWrite) - strlen($secondWrite)),
            $sink->getBlockRemainingByteCount()
        );
    }

    public function testFlush()
    {
        $mockedSink1 = $this->createMockedSink();
        $mockedSink1
            ->expects($this->once())
            ->method('flush');

        $mockedSink2 = $this->createMockedSink();
        $mockedSink2
            ->expects($this->once())
            ->method('flush');

        $sink = $this->sinkBuilder
            ->setConstructorArgs(array([$mockedSink1, $mockedSink2]))
            ->getMock();

        $sink->flush();
    }
}
