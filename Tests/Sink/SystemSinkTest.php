<?php

namespace EXSyst\Component\IO\Tests;

use EXSyst\Component\IO\Sink\SystemSink;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class SystemSinkTest extends AbstractSinkTest
{
    public function setUp()
    {
        $this->sinkBuilder = new SystemSinkBuilder();
    }

    public function testConstants()
    {
        $this->assertEquals(1024, SystemSink::BLOCK_BYTE_COUNT);
    }

    public function testInstanceGetter()
    {
        $this->assertInstanceOf('EXSyst\Component\IO\Sink\SystemSink', SystemSink::getInstance());
    }

    public function testDefaultValues()
    {
        $sink = $this->sinkBuilder->getMock();

        // Attributes
        $this->assertEquals(0, \PHPUnit_Framework_Assert::readAttribute($sink, 'written'));

        // Methods
        $this->assertEquals(0, $sink->getWrittenByteCount());
        $this->assertEquals(SystemSink::BLOCK_BYTE_COUNT, $sink->getBlockByteCount());
        $this->assertEquals(SystemSink::BLOCK_BYTE_COUNT, $sink->getBlockRemainingByteCount());
    }

    public function testWrite()
    {
        $sink = $this->sinkBuilder->getMock();

        // First test
        ob_start();
        $data = $this->getRandomData(44);
        $sink->write($data);

        $this->assertEquals($data, ob_get_clean());
        $this->assertEquals(44, \PHPUnit_Framework_Assert::readAttribute($sink, 'written'));
        $this->assertEquals(SystemSink::BLOCK_BYTE_COUNT - 44 % SystemSink::BLOCK_BYTE_COUNT, $sink->getBlockRemainingByteCount());

        // Second test
        ob_start();
        $data = $this->getRandomData(SystemSink::BLOCK_BYTE_COUNT + 20);
        $sink->write($data);

        $this->assertEquals($data, ob_get_clean());
        $this->assertEquals(SystemSink::BLOCK_BYTE_COUNT + 64, \PHPUnit_Framework_Assert::readAttribute($sink, 'written'));
        $this->assertEquals(SystemSink::BLOCK_BYTE_COUNT - 64 % SystemSink::BLOCK_BYTE_COUNT, $sink->getBlockRemainingByteCount());

    }
}

class SystemSinkBuilder
{
    public function getMock()
    {
        return SystemSink::getInstance();
    }
}
