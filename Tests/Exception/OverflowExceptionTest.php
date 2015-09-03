<?php

namespace EXSyst\Component\IO\Tests\Exception;

use EXSyst\Component\IO\Exception\OverflowException;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class OverflowExceptionTest extends AbstractExceptionTest
{
    public function setUp()
    {
        $this->exception = new OverflowException();
    }

    public function testInheritance()
    {
        $this->assertInstanceOf('OverflowException', $this->exception);
    }
}
