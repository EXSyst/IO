<?php

namespace EXSyst\Component\IO\Tests\Channel;

use EXSyst\Component\IO\Channel\ChannelInterface;
use EXSyst\Component\IO\SelectableInterface;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class ChannelInterfaceTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(SelectableInterface::class, $this->getMock(ChannelInterface::class));
    }
}
