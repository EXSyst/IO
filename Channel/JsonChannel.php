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

use EXSyst\Component\IO\Exception;
use EXSyst\Component\IO\Reader\CDataReader;
use EXSyst\Component\IO\Reader\JsonReader;
use EXSyst\Component\IO\Selectable;
use EXSyst\Component\IO\Sink\SinkInterface;
use EXSyst\Component\IO\Source\SourceInterface;

class JsonChannel implements ChannelInterface
{
    /**
     * @var JsonReader
     */
    private $source;
    /**
     * @var SinkInterface
     */
    private $sink;
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
     * @param SourceInterface $source         The source which will be used to receive messages
     * @param SinkInterface   $sink           The sink which will be used to send messages
     * @param int             $encoderOptions Options to pass to {@link http://php.net/json_encode json_encode}
     * @param bool            $assoc          true to decode JSON associative arrays into PHP associative arrays, false to decode them into PHP objects
     * @param int             $depth          Maximum JSON nesting depth to allow
     * @param int             $decoderOptions Options to pass to {@link http://php.net/json_encode json_decode}
     */
    public function __construct(SourceInterface $source, SinkInterface $sink, $encoderOptions = 0, $assoc = false, $depth = 512, $decoderOptions = 0)
    {
        $this->source = ($source instanceof JsonReader) ? $source : new JsonReader(CDataReader::fromSource($source));
        $this->sink = $sink;
        $this->encoderOptions = $encoderOptions;
        $this->assoc = $assoc;
        $this->depth = $depth;
        $this->decoderOptions = $decoderOptions;
    }

    /** {@inheritdoc} */
    public function getStream()
    {
        return Selectable::streamOf($this->source);
    }

    /** {@inheritdoc} */
    public function sendMessage($message)
    {
        $encodedMessage = json_encode($message, $this->encoderOptions, $this->depth);
        if ($encodedMessage === false) {
            throw new Exception\EncodingException(json_last_error_msg());
        }

        $this->sink->write($encodedMessage."\n");

        return $this;
    }

    /** {@inheritdoc} */
    public function receiveMessage()
    {
        return $this->source->readValue($this->assoc, $this->depth, $this->decoderOptions);
    }
}
