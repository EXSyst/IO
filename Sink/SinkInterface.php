<?php

namespace EXSyst\Component\IO\Sink;

use EXSyst\Component\IO\Exception;

/**
 * Represents a sink into which raw bytes can be written.
 *
 * @author Nicolas "Exter-N" L. <exter-n@exter-n.fr>
 *
 * @api
 */
interface SinkInterface
{
    /**
     * Counts how many bytes were written into the sink.
     *
     * @return int Number of bytes written into the sink
     *
     * @throws Exception\RuntimeException If an I/O operation fails
     *
     * @api
     */
    public function getWrittenByteCount();

    /**
     * Queries the sink's block size.
     *
     * For optimal performance, it is recommended to write data into a sink in blocks.
     *
     * @return int The sink's block size, in bytes
     *
     * @throws Exception\RuntimeException If an I/O operation fails
     *
     * @api
     */
    public function getBlockByteCount();

    /**
     * Counts how many bytes can be written before the sink's next block boundary.
     *
     * For optimal performance, it is recommended to write data into a sink in blocks.
     *
     * @return int Number of bytes before the sink's next block boundary
     *
     * @throws Exception\RuntimeException If an I/O operation fails
     *
     * @api
     */
    public function getBlockRemainingByteCount();

    /**
     * Writes data into the sink.
     *
     * @param string $data Data to write
     *
     * @return self
     *
     * @throws Exception\LengthException   If a negative amount is requested
     * @throws Exception\OverflowException If the data cannot be written because the sink is full
     * @throws Exception\RuntimeException  If an I/O operation fails
     *
     * @api
     */
    public function write($data);

    /**
     * Flushes all the written data on the underlying medium, if applicable.
     *
     * @return self
     *
     * @throws Exception\RuntimeException If an I/O operation fails
     *
     * @api
     */
    public function flush();
}
