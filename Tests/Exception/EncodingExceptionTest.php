<?php

namespace EXSyst\Component\IO\Tests\Exception;

use EXSyst\Component\IO\Exception;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class EncodingExceptionTest extends AbstractExceptionTest
{
    public function setUp()
    {
        $this->exception = new Exception\EncodingException();
    }

    public function testInheritance()
    {
        $this->assertInstanceOf(Exception\RuntimeException::class, $this->exception);
    }
}
