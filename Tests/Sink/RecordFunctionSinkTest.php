<?php

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
