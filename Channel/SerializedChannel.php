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

use EXSyst\Component\IO\Reader\CDataReader;
use EXSyst\Component\IO\Reader\SerializedReader;
use EXSyst\Component\IO\Selectable;
use EXSyst\Component\IO\Sink\SinkInterface;
use EXSyst\Component\IO\Source\SourceInterface;

class SerializedChannel implements ChannelInterface
{
    /**
     * @var SerializedReader
     */
    private $source;
    /**
     * @var SinkInterface
     */
    private $sink;

    /**
     * Constructor.
     *
     * @param SourceInterface $source The source which will be used to receive messages
     * @param SinkInterface   $sink   The sink which will be used to send messages
     */
    public function __construct(SourceInterface $source, SinkInterface $sink)
    {
        $this->source = ($source instanceof SerializedReader) ? $source : new SerializedReader(CDataReader::fromSource($source));
        $this->sink = $sink;
    }

    /** {@inheritdoc} */
    public function getStream()
    {
        return Selectable::streamOf($this->source);
    }

    /** {@inheritdoc} */
    public function sendMessage($message)
    {
        $this->sink->write(serialize($message));

        return $this;
    }

    /** {@inheritdoc} */
    public function receiveMessage()
    {
        return $this->source->readValue();
    }
}
