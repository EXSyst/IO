<?php

namespace EXSyst\Component\IO\Tests;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
abstract class AbstractSinkTest extends \PHPUnit_Framework_TestCase
{
    protected $sinkBuilder;

    public function testInterface() {
        $this->assertInstanceOf('EXSyst\Component\IO\Sink\SinkInterface', $this->sinkBuilder->getMock());
    }

    public function testWriteReturnSelf() {
        $sink = $this->sinkBuilder->getMock();
        $this->assertEquals($sink, $sink->write(''));
    }

    public function testFlushReturnSelf() {
        $sink = $this->sinkBuilder->getMock();
        $this->assertEquals($sink, $sink->flush());
    }

    public function createMockedSink() {
        return $this->getMock('EXSyst\Component\IO\Sink\SinkInterface');
    }

    /**
     * @var int $length
     */
    protected function getRandomData($length) {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789';
        $charactersLength = strlen($characters);

        $randomData = '';
        for ($i = 0; $i < $length; $i++) {
            $randomData .= $characters[$i % $charactersLength];
        }
        return $randomData;
    }
}
