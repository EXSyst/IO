<?php

namespace EXSyst\Component\IO\Tests\Exception;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class InvalidArgumentExceptionTest extends AbstractExceptionTest
{
    public function setUp()
    {
        $this->exception = $this->getMock('EXSyst\Component\IO\Exception\InvalidArgumentException');
    }

    public function testInheritance()
    {
        $this->assertInstanceOf('InvalidArgumentException', $this->exception);
    }
}
