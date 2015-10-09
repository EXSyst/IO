<?php

/*
 * This file is part of the IO package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
