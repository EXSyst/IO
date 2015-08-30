<?php

namespace EXSyst\Component\IO\Source;

use LengthException;
use UnderflowException;
use EXSyst\Component\IO\SourceInterface;
use EXSyst\Component\IO\Source\Internal\StringSourceState;

class StringSource implements SourceInterface
{
    /**
     * @var string
     */
    public $data;
    /**
     * @var int
     */
    private $start;
    /**
     * @var int
     */
    public $end;
    /**
     * @var int
     */
    public $offset;

    public function __construct($data, $start = 0, $end = null)
    {
        $this->data = strval($data);
        $this->start = intval($start);
        $size = strlen($this->data);
        $this->end = ($end === null) ? $size : min($size, intval($end));
        $this->offset = $this->start;
    }

    /** {@inheritdoc} */
    public function getConsumedByteCount()
    {
        return $this->offset - $this->start;
    }

    /** {@inheritdoc} */
    public function getRemainingByteCount()
    {
        return $this->end - $this->offset;
    }

    /** {@inheritdoc} */
    public function isFullyConsumed()
    {
        return $this->offset >= $this->end;
    }

    /** {@inheritdoc} */
    public function wouldBlock($byteCount, $allowIncomplete = false)
    {
        return false;
    }

    /** {@inheritdoc} */
    public function getBlockByteCount()
    {
        return 1;
    }

    /** {@inheritdoc} */
    public function getBlockRemainingByteCount()
    {
        return 1;
    }

    /** {@inheritdoc} */
    public function captureState()
    {
        return new StringSourceState($this->offset);
    }

    private function checkByteCount(&$byteCount, $allowIncomplete)
    {
        if ($byteCount < 0) {
            throw new LengthException('The byte count must not be negative');
        }
        $maxByteCount = $this->getRemainingByteCount();
        if (($maxByteCount < 0 && $byteCount > 0 || $maxByteCount < $byteCount) && !$allowIncomplete) {
            throw new UnderflowException('The source doesn\'t have enough remaining data to fulfill the request');
        }
        $byteCount = min($byteCount, $maxByteCount);
    }

    /** {@inheritdoc} */
    public function read($byteCount, $allowIncomplete = false)
    {
        $this->checkByteCount($byteCount, $allowIncomplete);
        if ($byteCount <= 0) {
            return '';
        }
        $substr = substr($this->data, $this->offset, $byteCount);
        $this->offset += $byteCount;

        return $substr;
    }

    /** {@inheritdoc} */
    public function peek($byteCount, $allowIncomplete = false)
    {
        $this->checkByteCount($byteCount, $allowIncomplete);
        if ($byteCount <= 0) {
            return '';
        }

        return substr($this->data, $this->offset, $byteCount);
    }

    /** {@inheritdoc} */
    public function skip($byteCount, $allowIncomplete = false)
    {
        $this->checkByteCount($byteCount, $allowIncomplete);
        if ($byteCount <= 0) {
            return 0;
        }
        $this->offset += $byteCount;

        return $byteCount;
    }

    public function __toString()
    {
        return $this->data;
    }
}
