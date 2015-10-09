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

use EXSyst\Component\IO\Sink\StringSink;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class StringSinkTest extends AbstractSinkTest
{
    public function setUp()
    {
        $this->sinkBuilder = $this->getMockBuilder('EXSyst\Component\IO\Sink\StringSink')
            ->setMethods(null);
    }

    public function testDefaultValues()
    {
        $sink = $this->sinkBuilder->getMock();

        // Attributes
        $this->assertEquals([], \PHPUnit_Framework_Assert::readAttribute($sink, 'data'));
        $this->assertEquals(0, \PHPUnit_Framework_Assert::readAttribute($sink, 'length'));

        // Methods
        $this->assertEquals(0, $sink->getWrittenByteCount());
        $this->assertEquals(1, $sink->getBlockByteCount());
        $this->assertEquals(1, $sink->getBlockRemainingByteCount());
    }

    public function testEmptyDataWrite()
    {
        $sink = $this->sinkBuilder->getMock();
        $sink->write('');

        $this->assertEquals([], \PHPUnit_Framework_Assert::readAttribute($sink, 'data'));
        $this->assertEquals(0, \PHPUnit_Framework_Assert::readAttribute($sink, 'length'));
    }

    public function testWrite()
    {
        $sink = $this->sinkBuilder->getMock();
        $data1 = $this->getRandomData(3);
        $data2 = $this->getRandomData(4);
        $data3 = $this->getRandomData(StringSink::MAX_CONCAT_LENGTH - 6);

        $sink->write($data1);
        $this->assertEquals([$data1], \PHPUnit_Framework_Assert::readAttribute($sink, 'data'));
        $this->assertEquals(3, \PHPUnit_Framework_Assert::readAttribute($sink, 'length'));

        $sink->write($data2);
        $this->assertEquals([$data1.$data2], \PHPUnit_Framework_Assert::readAttribute($sink, 'data'));
        $this->assertEquals(7, \PHPUnit_Framework_Assert::readAttribute($sink, 'length'));

        $sink->write($data3);
        $this->assertEquals([$data1.$data2, $data3], \PHPUnit_Framework_Assert::readAttribute($sink, 'data'));
        $this->assertEquals(StringSink::MAX_CONCAT_LENGTH + 1, \PHPUnit_Framework_Assert::readAttribute($sink, 'length'));

        $this->assertEquals($data1.$data2.$data3, strval($sink));
    }
}
