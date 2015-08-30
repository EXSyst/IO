<?php

namespace EXSyst\Component\IO;

/**
 * Represents a source from which raw bytes can be read.
 *
 * @author Nicolas "Exter-N" L. <exter-n@exter-n.fr>
 *
 * @api
 */
interface SourceInterface
{
    /**
     * Counts how many bytes were consumed from the source.
     *
     * @return int Number of bytes consumed from the source
     *
     * @throws \RuntimeException If an I/O operation fails
     *
     * @api
     */
    public function getConsumedByteCount();

    /**
     * Counts, if possible, how many bytes remain in the source.
     *
     * For example, it is possible if the source is fully in memory or if it represents a regular file or a network resource whose size was advertised as a header.
     * Conversely, it is not possible, for example, if the source represents a raw pipe or network connection.
     *
     * @return int|null Number of bytes remaining in the source, or null if it can't be determined
     *
     * @throws \RuntimeException If an I/O operation fails
     *
     * @api
     */
    public function getRemainingByteCount();

    /**
     * Determines whether the source is fully consumed.
     *
     * If a source is fully consumed, it won't return any data when tried to read from, neither will it block.
     * A source may not become aware of the fact that it is fully consumed without trying to actually read from it (this is the case of, for example, network connections). This method may return false negatives in this case.
     *
     * @return bool true if the source is definitely fully consumed, false if it may contain more data
     *
     * @throws \RuntimeException If an I/O operation fails
     *
     * @api
     */
    public function isFullyConsumed();

    /**
     * Determines whether the source would block if tried to read or skip from.
     *
     * @param int  $byteCount       Number of bytes to test
     * @param bool $allowIncomplete true to test for any amount of data smaller than or equal to the requested amount, false (default) to test for the exact requested amount and no less
     *
     * @return bool true if the source would block, false if it would return (or throw) immediately
     *
     * @throws \RuntimeException If an I/O operation fails
     *
     * @api
     */
    public function wouldBlock($byteCount, $allowIncomplete = false);

    /**
     * Queries the source's block size.
     *
     * For optimal performance, it is recommended to read data from a source in blocks.
     *
     * @return int The source's block size, in bytes
     *
     * @throws \RuntimeException If an I/O operation fails
     *
     * @api
     */
    public function getBlockByteCount();

    /**
     * Counts how many bytes remain before the source's next block boundary.
     *
     * For optimal performance, it is recommended to read data from a source in blocks.
     *
     * @return int Number of bytes before the source's next block boundary
     *
     * @throws \RuntimeException If an I/O operation fails
     *
     * @api
     */
    public function getBlockRemainingByteCount();

    /**
     * Captures the source's current state, which then may be subsequently restored.
     *
     * @return StateInterface The source's current state
     *
     * @throws \RuntimeException If an I/O operation fails
     * @throws \LogicException   If the source doesn't support restoring a previous state
     *
     * @api
     */
    public function captureState();

    /**
     * Reads and consumes data from the source.
     *
     * @param int  $byteCount       Number of bytes to read
     * @param bool $allowIncomplete true to accept any amount of data smaller than or equal to the requested amount, false (default) to throw an exception if the exact requested amount cannot be read
     *
     * @return string The data that was just read
     *
     * @throws \LengthException    If a negative amount is requested
     * @throws \UnderflowException If the exact requested amount cannot be read and the caller doesn't allow an incomplete read
     * @throws \RuntimeException   If an I/O operation fails
     *
     * @api
     */
    public function read($byteCount, $allowIncomplete = false);

    /**
     * Reads data from the source, without consuming it, in order to keep it available for subsequent operations.
     *
     * @param int  $byteCount       Number of bytes to read
     * @param bool $allowIncomplete true to accept any amount of data smaller than or equal to the requested amount, false (default) to throw an exception if the exact requested amount cannot be read
     *
     * @return string The data that was just read
     *
     * @throws \LengthException    If a negative amount is requested
     * @throws \UnderflowException If the exact requested amount cannot be read and the caller doesn't allow an incomplete read
     * @throws \RuntimeException   If an I/O operation fails
     * @throws \LogicException     If the current source doesn't support reading data without consuming it
     *
     * @api
     */
    public function peek($byteCount, $allowIncomplete = false);

    /**
     * Consumes data from the source, without reading it.
     *
     * If the source has ad hoc support for this function, it should be much more I/O- and memory-efficient than reading the data and just taking the byte count of the result. Otherwise, it should be equivalent.
     *
     * @param int  $byteCount       Number of bytes to skip
     * @param bool $allowIncomplete true to accept skipping any amount of data smaller than or equal to the requested amount, false (default) to throw an exception if the exact requested amount cannot be skipped
     *
     * @return int Number of bytes that were just skipped
     *
     * @throws \LengthException    If a negative amount is requested
     * @throws \UnderflowException If the exact requested amount cannot be skipped and the caller doesn't allow an incomplete skip
     * @throws \RuntimeException   If an I/O operation fails
     *
     * @api
     */
    public function skip($bytecount, $allowIncomplete = false);
}
