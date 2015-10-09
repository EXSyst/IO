<?php

/*
 * This file is part of the IO package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Component\IO\Tests\Exception;

use EXSyst\Component\IO\Exception\InvalidArgumentException;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class InvalidArgumentExceptionTest extends AbstractExceptionTest
{
    public function setUp()
    {
        $this->exception = new InvalidArgumentException();
    }

    public function testInheritance()
    {
        $this->assertInstanceOf('InvalidArgumentException', $this->exception);
    }
}
