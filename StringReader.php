<?php

/*
 * This file is part of the IO package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Component\IO;

class StringReader
{
    private $data;
    private $start;
    private $end;
    private $offset = 0;
    private $line = 0;
    private $row = 0;

    /**
     * @param string   $data
     * @param int      $start
     * @param int|null $end
     */
    public function __construct($data, $start = 0, $end = null)
    {
        $this->data = strval($data);
        $this->start = intval($start);
        $size = strlen($this->data);
        $this->end = null === $end ? $size : min($size, $end);

        if (0 !== $start) {
            $this->moveCursor(substr($data, 0, $start));
        }
    }

    /**
     * @param callable $transactionFn
     * @param bool     $restoreOnFalseReturn
     *
     * @return mixed $transactionFn return
     */
    public function transact(callable $transactionFn, $restoreOnFalseReturn = false)
    {
        $state = $this->captureState();
        try {
            $retval = call_user_func($transactionFn);
        } catch (\Exception $ex) {
            $state->restore();
            throw $ex;
        }

        if ($restoreOnFalseReturn && false === $retval) {
            $state->restore();
        }

        return $retval;
    }

    /**
     * @param string[]|\Traversable
     *
     * @param string|null
     */
    public function eatAny($strings) {
        foreach ($strings as $string) {
            if ($this->eat($string)) {
                return $string;
            }
        }
    }

    /**
     * @param string $string
     *
     * @return bool
     */
    public function eat($string)
    {
        $state = $this->captureState();
        if ($string === $this->read(strlen($string), true)) {
            return true;
        } else {
            $state->restore();

            return false;
        }
    }

    /**
     * @param string   $mask
     * @param int|null $maxLength
     *
     * @return string
     */
    public function eatSpan($mask, $maxLength = null)
    {
        if (null === $maxLength) {
            $maxLength = $this->getRemainingByteCount();
        }

        return $this->read(strspn($this->data, $mask, $this->offset, $maxLength));
    }

    /**
     * @param string   $mask
     * @param int|null $maxLength
     *
     * @return string
     */
    public function eatCSpan($mask, $maxLength = null)
    {
        if (null === $maxLength) {
            $maxLength = $this->getRemainingByteCount();
        }

        return $this->read(strcspn($this->data, $mask, $this->offset, $maxLength));
    }

    public function getOffset()
    {
        return $this->offset;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function getRow()
    {
        return $this->row;
    }

    /**
     * Counts how many bytes were consumed from the source.
     *
     * @return int
     */
    public function getConsumedByteCount()
    {
        return $this->offset - $this->start;
    }

    /**
     * Counts how many bytes remain in the source.
     *
     * @return int
     */
    public function getRemainingByteCount()
    {
        return $this->end - $this->offset;
    }

    /**
     * Determines whether the source is fully consumed.
     *
     * @return bool
     */
    public function isFullyConsumed()
    {
        return $this->offset >= $this->end;
    }

    /**
     * Reads and consumes data from the source.
     *
     * @param int  $byteCount       Number of bytes to read
     * @param bool $allowIncomplete true to accept any amount of data smaller than or equal to the requested amount, false (default) to throw an exception if the exact requested amount cannot be read
     *
     * @throws Exception\UnderflowException If the exact requested amount cannot be read
     *
     * @return string The data that was just read
     */
    public function read($byteCount, $allowIncomplete = false)
    {
        $maxByteCount = $this->getRemainingByteCount();
        if (!$allowIncomplete && $maxByteCount < $byteCount) {
            throw new Exception\UnderflowException('The source doesn\'t have enough remaining data to fulfill the request');
        }

        $byteCount = max(min($byteCount, $maxByteCount), 0);
        $substr = substr($this->data, $this->offset, $byteCount);
        $this->moveCursor($substr);

        return $substr;
    }

    /**
     * Captures the source's current state, which then may be subsequently restored.
     *
     * @return StateInterface The source's current state
     */
    public function captureState()
    {
        return new StringSourceState($this->offset, $this->line, $this->row);
    }

    /**
     * @return string This object's string representation
     */
    public function __toString()
    {
        return $this->data;
    }

    private function moveCursor(string $value = null)
    {
        $this->offset += strlen($value);
        $this->row += strlen($value);
        if ($lines = preg_match_all("/\r\n?|\n\r?/", $value)) {
            $this->line += $lines;
            $this->row = strlen($value) - max(strrpos($value, "\r"), strrpos($value, "\n")) - 1;
        }
    }
}
