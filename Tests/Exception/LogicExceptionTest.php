<?php

/*
 * This file is part of the IO package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Component\IO\Tests\Exception;

use EXSyst\Component\IO\Exception\LogicException;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class LogicExceptionTest extends AbstractExceptionTest
{
    public function setUp()
    {
        $this->exception = new LogicException();
    }

    public function testInheritance()
    {
        $this->assertInstanceOf('LogicException', $this->exception);
    }
}
