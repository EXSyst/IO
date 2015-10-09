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
use EXSyst\Component\IO\SelectableInterface;

/**
 * Represents a channel, which can be used to send and receive messages to and from another task, which may be running in another fiber, thread or process, or on another machine.
 * A channel may use some kind of serialization, which may prevent some data types to be sent or received, or may lose some type information.
 *
 * @author Nicolas "Exter-N" L. <exter-n@exter-n.fr>
 *
 * @api
 */
interface ChannelInterface extends SelectableInterface
{
    /**
     * Sends a message to the other task.
     *
     * @param mixed $message The message to send
     *
     * @throws Exception\RuntimeException  If an I/O operation fails
     * @throws Exception\EncodingException If an error occurs while encoding or decoding data.
     *
     * @return $this
     *
     * @api
     */
    public function sendMessage($message);

    /**
     * Receives a message from the other task.
     *
     * @throws Exception\UnderflowException If the other task has closed the connection
     * @throws Exception\RuntimeException   If an I/O operation fails
     *
     * @return mixed The received message
     *
     * @api
     */
    public function receiveMessage();
}
