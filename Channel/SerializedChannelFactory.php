<?php

/*
 * This file is part of the IO package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Component\IO\Channel;

use EXSyst\Component\IO\Sink\SinkInterface;
use EXSyst\Component\IO\Source\SourceInterface;

final class SerializedChannelFactory implements ChannelFactoryInterface
{
    /**
     * @var SerializedChannelFactory
     */
    private static $instance;

    private function __construct()
    {
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /** {@inheritdoc} */
    public function createChannel(SourceInterface $source, SinkInterface $sink)
    {
        return new SerializedChannel($source, $sink);
    }
}
