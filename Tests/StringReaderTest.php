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

use EXSyst\Component\IO\StringReader;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class StringReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testEat()
    {
        $reader = new StringReader('Foo bar exsyst');

        $this->assertTrue($reader->eat('Fo'));
        $this->assertFalse($reader->eat('bar'));
        $this->assertEquals('o ', $reader->read(2));
        $this->assertTrue($reader->eat('bar exsyst'));
        $this->assertTrue($reader->isFullyConsumed());
    }
}
