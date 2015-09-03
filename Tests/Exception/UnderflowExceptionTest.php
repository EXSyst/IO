<?php

namespace EXSyst\Component\IO\Tests\Exception;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class UnderflowExceptionTest extends AbstractExceptionTest
{
    public function setUp()
    {
        $this->exception = $this->getMock('EXSyst\Component\IO\Exception\UnderflowException');
    }

    public function testInheritance()
    {
        $this->assertInstanceOf('UnderflowException', $this->exception);
    }
}
