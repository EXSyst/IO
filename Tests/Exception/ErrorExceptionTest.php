<?php

namespace EXSyst\Component\IO\Tests\Exception;

use EXSyst\Component\IO\Exception\ErrorException;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class ErrorExceptionTest extends AbstractExceptionTest
{
    public function setUp()
    {
        $this->exception = new ErrorException();
    }

    public function testInheritance()
    {
        $this->assertInstanceOf('ErrorException', $this->exception);
    }
}
