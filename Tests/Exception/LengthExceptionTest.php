<?php

namespace EXSyst\Component\IO\Tests\Exception;

use EXSyst\Component\IO\Exception\LengthException;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class LengthExceptionTest extends AbstractExceptionTest
{
    public function setUp()
    {
        $this->exception = new LengthException();
    }

    public function testInheritance()
    {
        $this->assertInstanceOf('LengthException', $this->exception);
    }
}
