<?php

/*
 * This file is part of the IO package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Component\IO\Tests;

/**
 * @author Ener-Getick <egetick@gmail.com>
 *
 * TODO
 */
class RecordFunctionSinkTest extends AbstractSinkTest
{
    public function setUp()
    {
        $this->sinkBuilder = $this->getMockBuilder('EXSyst\Component\IO\Sink\RecordFunctionSink')
            ->setConstructorArgs([
                function () {},
            ])
            ->setMethods(null);
    }
}
