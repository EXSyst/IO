<?php

namespace EXSyst\Component\IO\Tests\Exception;

use EXSyst\Component\IO\Exception\UnderflowException;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class UnderflowExceptionTest extends AbstractExceptionTest
{
    public function setUp()
    {
        $this->exception = new UnderflowException();
    }

    public function testInheritance()
    {
        $this->assertInstanceOf('UnderflowException', $this->exception);
    }
}
