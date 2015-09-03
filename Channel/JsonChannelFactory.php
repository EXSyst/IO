<?php

namespace EXSyst\Component\IO\Channel;

use EXSyst\Component\IO\Sink\SinkInterface;
use EXSyst\Component\IO\Source\SourceInterface;

class JsonChannelFactory implements ChannelFactoryInterface
{
    /**
     * @var int
     */
    private $encoderOptions;
    /**
     * @var bool
     */
    private $assoc;
    /**
     * @var int
     */
    private $depth;
    /**
     * @var int
     */
    private $decoderOptions;

    /**
     * Constructor.
     *
     * @param int  $encoderOptions Options to pass to {@link http://php.net/json_encode json_encode}
     * @param bool $assoc          true to decode JSON associative arrays into PHP associative arrays, false to decode them into PHP objects
     * @param int  $depth          Maximum JSON nesting depth to allow
     * @param int  $decoderOptions Options to pass to {@link http://php.net/json_encode json_decode}
     */
    public function __construct($encoderOptions = 0, $assoc = false, $depth = 512, $decoderOptions = 0)
    {
        $this->encoderOptions = $encoderOptions;
        $this->assoc = $assoc;
        $this->depth = $depth;
        $this->decoderOptions = $decoderOptions;
    }

    /** {@inheritdoc} */
    public function createChannel(SourceInterface $source, SinkInterface $sink)
    {
        return new JsonChannel($source, $sink, $this->encoderOptions, $this->assoc, $this->depth, $this->decoderOptions);
    }
}
