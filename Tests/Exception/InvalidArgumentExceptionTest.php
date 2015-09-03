<?php

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
