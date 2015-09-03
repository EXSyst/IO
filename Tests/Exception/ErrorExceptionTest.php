<?php

namespace EXSyst\Component\IO\Tests\Exception;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class ErrorExceptionTest extends AbstractExceptionTest
{
    public function setUp()
    {
        $this->exception = $this->getMock('EXSyst\Component\IO\Exception\ErrorException');
    }

    public function testInheritance()
    {
        $this->assertInstanceOf('ErrorException', $this->exception);
    }
}
