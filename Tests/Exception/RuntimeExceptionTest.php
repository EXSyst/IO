<?php

namespace EXSyst\Component\IO\Tests\Exception;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class RuntimeExceptionTest extends AbstractExceptionTest
{
    public function setUp()
    {
        $this->exception = $this->getMock('EXSyst\Component\IO\Exception\RuntimeException');
    }

    public function testInheritance()
    {
        $this->assertInstanceOf('RuntimeException', $this->exception);
    }
}
