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
