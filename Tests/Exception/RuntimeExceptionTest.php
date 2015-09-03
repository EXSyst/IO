<?php

namespace EXSyst\Component\IO\Tests\Exception;

use EXSyst\Component\IO\Exception\RuntimeException;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class RuntimeExceptionTest extends AbstractExceptionTest
{
    public function setUp()
    {
        $this->exception = new RuntimeException();
    }

    public function testInheritance()
    {
        $this->assertInstanceOf('RuntimeException', $this->exception);
    }
}
