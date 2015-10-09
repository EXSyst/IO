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

/**
 * Specifies an encoder/decoder couple, along with their parameters, to allow creating channels using them.
 *
 * @author Nicolas "Exter-N" L. <exter-n@exter-n.fr>
 *
 * @api
 */
interface ChannelFactoryInterface
{
    /**
     * Creates a channel with a given source and sink, and the encoder/decoder specified by the current factory.
     *
     * @param SourceInterface $source The source which will be used to receive messages
     * @param SinkInterface   $sink   The sink which will be used to send messages
     *
     * @return ChannelInterface The created channel
     *
     * @api
     */
    public function createChannel(SourceInterface $source, SinkInterface $sink);
}
