<?php

namespace EXSyst\Component\IO\Tests\Exception;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class OverflowExceptionTest extends AbstractExceptionTest
{
    public function setUp()
    {
        $this->exception = $this->getMock('EXSyst\Component\IO\Exception\OverflowException');
    }

    public function testInheritance()
    {
        $this->assertInstanceOf('OverflowException', $this->exception);
    }
}
