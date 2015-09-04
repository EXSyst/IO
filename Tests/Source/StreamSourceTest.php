<?php

namespace EXSyst\Component\IO\Tests\Source;

use EXSyst\Component\IO\Tests\StreamWrapper\WrapperHandler;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class StreamSourceTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->stream = WrapperHandler::open(['foo' => 'bar']);
        $this->sourceBuilder = $this->getMockBuilder('EXSyst\Component\IO\Source\StreamSource')
            ->setConstructorArgs([$this->stream])
            ->setMethods(null);
    }

    /**
     * @expectedException EXSyst\Component\IO\Exception\InvalidArgumentException
     * @expectedExceptionMessage The stream must be a resource
     */
    public function testInvalidStream()
    {
        $this->sourceBuilder
            ->setConstructorArgs([new \stdClass()])
            ->getMock();
    }

    /**
     * @expectedException EXSyst\Component\IO\Exception\InvalidArgumentException
     * @expectedExceptionMessage The stream must be a suitable resource
     */
    public function testInvalidStreamType()
    {
        $this->sourceBuilder
            ->setConstructorArgs([imagecreate(1, 1)])
            ->getMock();
    }

    /**
     * @expectedException EXSyst\Component\IO\Exception\InvalidArgumentException
     * @expectedExceptionMessage The on-close function must be callable
     */
    public function testInvalidOnCloseCallable()
    {
        $this->sourceBuilder
            ->setConstructorArgs([$this->stream, false, ['foo']])
            ->getMock();
    }

    public function testConstructor()
    {
        $statCalls = 0;
        $tellCalls = 0;
        $stream = WrapperHandler::open([
            'stat' => function () use (&$statCalls) {
                ++$statCalls;

                return 'foo';
            },
            // 'tell' => function () use (&$tellCalls) {
            //     $tellCalls++;
            //     return 20;
            // },
        ]);
        fseek($stream, 0);

        $callable = function () {};
        $source = $this->sourceBuilder
            ->setConstructorArgs([$stream, true, $callable])
            ->getMock();

        $this->assertEquals($stream, \PHPUnit_Framework_Assert::readAttribute($source, 'stream'));
        $this->assertTrue(\PHPUnit_Framework_Assert::readAttribute($source, 'streamOwner'));
        $this->assertEquals($callable, \PHPUnit_Framework_Assert::readAttribute($source, 'onClose'));
        $this->assertEquals(false, \PHPUnit_Framework_Assert::readAttribute($source, 'seekable'));
        // $this->assertEquals(20, \PHPUnit_Framework_Assert::readAttribute($source, 'baseCursor'));

        $this->assertEquals($stream, $source->getStream());
        $this->assertTrue($source->isStreamOwner());
        $this->assertFalse($source->isSeekable());

        $this->assertEquals(1, $statCalls);
        // $this->assertEquals(1, $tellCalls);
    }

    /**
     * TODO: Find a way to test the fclose function.
     */
    public function testDestruction()
    {
        $closeCalls = 0;

        $callable = function () use (&$closeCalls) {
            ++$closeCalls;
        };
        $source = $this->sourceBuilder
            ->setConstructorArgs([$this->stream, true, $callable])
            ->getMock();

        $source->__destruct();

        $this->assertEquals(1, $closeCalls);
        $this->assertFalse(\PHPUnit_Framework_Assert::readAttribute($source, 'streamOwner'));
    }

    public function testConsumedByteCountGetter()
    {
    }
}
