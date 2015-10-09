<?php

/*
 * This file is part of the IO package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Component\IO\Tests;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
abstract class AbstractSinkTest extends \PHPUnit_Framework_TestCase
{
    protected $sinkBuilder;

    public function testInterface()
    {
        $this->assertInstanceOf('EXSyst\Component\IO\Sink\SinkInterface', $this->sinkBuilder->getMock());
    }

    public function testWriteReturnSelf()
    {
        $sink = $this->sinkBuilder->getMock();
        $this->assertEquals($sink, $sink->write(''));
    }

    public function testFlushReturnSelf()
    {
        $sink = $this->sinkBuilder->getMock();
        $this->assertEquals($sink, $sink->flush());
    }

    public function createMockedSink()
    {
        return $this->getMock('EXSyst\Component\IO\Sink\SinkInterface');
    }

    /**
     * @var int
     */
    protected function getRandomData($length)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789';
        $charactersLength = strlen($characters);

        $randomData = '';
        for ($i = 0; $i < $length; ++$i) {
            $randomData .= $characters[$i % $charactersLength];
        }

        return $randomData;
    }
}
