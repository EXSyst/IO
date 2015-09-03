<?php

namespace EXSyst\Component\IO\Tests\Exception;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class LengthExceptionTest extends AbstractExceptionTest
{
    public function setUp()
    {
        $this->exception = $this->getMock('EXSyst\Component\IO\Exception\LengthException');
    }

    public function testInheritance()
    {
        $this->assertInstanceOf('LengthException', $this->exception);
    }
}
