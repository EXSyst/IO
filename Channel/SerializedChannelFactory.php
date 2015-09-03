<?php

namespace EXSyst\Component\IO\Channel;

use EXSyst\Component\IO\Sink\SinkInterface;
use EXSyst\Component\IO\Source\SourceInterface;

class SerializedChannelFactory implements ChannelFactoryInterface
{
    /**
     * @var SerializedChannelFactory
     */
    private static $instance;

    private function __construct()
    {
    }

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
