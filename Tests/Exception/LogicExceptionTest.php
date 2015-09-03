<?php

namespace EXSyst\Component\IO\Tests\Exception;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class LogicExceptionTest extends AbstractExceptionTest
{
    public function setUp()
    {
        $this->exception = $this->getMock('EXSyst\Component\IO\Exception\LogicException');
    }

    public function testInheritance()
    {
        $this->assertInstanceOf('LogicException', $this->exception);
    }
}
