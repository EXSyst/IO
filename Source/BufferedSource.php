<?php

namespace EXSyst\Component\IO\Source;

use EXSyst\Component\IO\Exception;
use EXSyst\Component\IO\Source;
use EXSyst\Component\IO\Source\Internal\BufferedSourceBuffer;
use EXSyst\Component\IO\Source\Internal\BufferedSourceState;
use EXSyst\Component\IO\Sink\StringSink;

class BufferedSource extends OuterSource
{
    /**
     * @var BufferedSourceBuffer
     */
    private $firstBuffer;
    /**
     * @var BufferedSourceBuffer
     */
    private $lastBuffer;
    /**
     * @var int
     */
    private $cursor;
    /**
     * @var int
     */
    private $stateCount;

    /**
     * @param SourceInterface $source
     */
    public function __construct(SourceInterface $source)
    {
        parent::__construct($source);
        $this->firstBuffer = new BufferedSourceBuffer();
        $this->lastBuffer = $this->firstBuffer;
        $this->cursor = 0;
        $this->stateCount = 0;
    }

    /**
     * @param int  $byteCount
     * @param bool $allowIncomplete
     *
     * @return bool
     */
    private function readFromInnerSource($byteCount, $allowIncomplete)
    {
        $sourceBlkSize = $this->source->getBlockByteCount();
        if ($sourceBlkSize === null || $sourceBlkSize < Source::MIN_BLOCK_BYTE_COUNT) {
            $sourceBlkSize = Source::MIN_BLOCK_BYTE_COUNT;
        }
        if ($byteCount === null || $byteCount < $sourceBlkSize) {
            $byteCount = $sourceBlkSize;
        }
        if ($allowIncomplete && $this->source->wouldBlock($byteCount, true)) {
            return false;
        }
        $data = $this->source->read($byteCount, true);
        if (empty($data)) {
            return false;
        }
        $dataLength = strlen($data);
        $last = $this->lastBuffer;
        if (!$last->length || $last->length + $dataLength <= StringSink::MAX_CONCAT_LENGTH) {
            $last->data .= $data;
            $last->length += $dataLength;
        } else {
            $newLast = new BufferedSourceBuffer($last->offset + $last->length);
            $newLast->data = $data;
            $newLast->length = $dataLength;
            $last->next = $newLast;
            $this->lastBuffer = $newLast;
        }

        return true;
    }

    /**
     * @param int                  $byteCount
     * @param BufferedSourceBuffer $firstBuffer
     * @param int                  $cursor
     *
     * @return string|bool
     */
    private static function readFromSingleBuffer(&$byteCount, BufferedSourceBuffer &$firstBuffer, &$cursor)
    {
        if ($cursor == $firstBuffer->length) {
            if ($firstBuffer->next) {
                $firstBuffer = $firstBuffer->next;
                $cursor = 0;
            } else {
                return false;
            }
        }
        $len = min($byteCount, $firstBuffer->length - $cursor);
        $data = ($cursor == 0 && $len == $firstBuffer->length) ? $firstBuffer->data : substr($firstBuffer->data, $cursor, $len);
        $cursor += $len;
        $byteCount -= $len;
        if ($cursor == $firstBuffer->length && $firstBuffer->next) {
            $firstBuffer = $firstBuffer->next;
            $cursor = 0;
        }

        return $data;
    }

    /**
     * @var int
     * @var BufferedSourceBuffer
     * @var int
     *
     * @return string
     */
    private static function readFromBuffers($byteCount, BufferedSourceBuffer &$firstBuffer, &$cursor)
    {
        $accumulator = [];
        while ($byteCount > 0) {
            $data = self::readFromSingleBuffer($byteCount, $firstBuffer, $cursor);
            if ($data === false) {
                break;
            }
            $accumulator[] = $data;
        }

        return implode($accumulator);
    }

    /**
     * @param int $byteCount
     * @param int $minByteCount
     *
     * @return bool
     */
    private function ensureVirtualBufferByteCount($byteCount, $minByteCount)
    {
        while (($bufsize = $this->getVirtualBufferByteCount()) < $byteCount) {
            if (!$this->readFromInnerSource(null, $bufsize >= $minByteCount)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param int  $byteCount
     * @param bool $allowIncomplete
     *
     * @return bool
     */
    private function ensureRemainingBufferByteCount($byteCount, $allowIncomplete)
    {
        $cursor = $this->getConsumedByteCount();

        return $this->ensureVirtualBufferByteCount($cursor + $byteCount, $allowIncomplete ? ($cursor + 1) : ($cursor + $byteCount));
    }

    /** {@inheritdoc} */
    public function getConsumedByteCount()
    {
        return $this->firstBuffer->offset + $this->cursor;
    }

    /**
     * @return int
     */
    private function getVirtualBufferByteCount()
    {
        return $this->lastBuffer->offset + $this->lastBuffer->length;
    }

    /**
     * @return int
     */
    private function getRemainingBufferByteCount()
    {
        return $this->getVirtualBufferByteCount() - $this->getConsumedByteCount();
    }

    /** {@inheritdoc} */
    public function getRemainingByteCount()
    {
        $remain = $this->source->getRemainingByteCount();
        if ($remain === null) {
            return;
        }

        return $this->getRemainingBufferByteCount() + $remain;
    }

    /** {@inheritdoc} */
    public function isFullyConsumed()
    {
        if ($this->lastBuffer->offset + $this->lastBuffer->length - $this->firstBuffer->offset - $this->cursor > 0) {
            return false;
        }

        return $this->source->isFullyConsumed();
    }

    /** {@inheritdoc} */
    public function wouldBlock($byteCount, $allowIncomplete = false)
    {
        $remain = $this->getRemainingBufferByteCount();
        if ($remain >= $byteCount || $remain > 0 && $allowIncomplete) {
            return false;
        }

        return $this->source->wouldBlock($byteCount - $remain, $allowIncomplete);
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
        return new BufferedSourceState($this->firstBuffer, $this->cursor, $this->stateCount);
    }

    /**
     * @param int  $byteCount
     * @param bool $allowIncomplete
     * @param bool $skipping
     *
     * @throws Exception\LengthException
     * @throws Exception\UnderflowException
     */
    private function checkByteCount(&$byteCount, $allowIncomplete, $skipping)
    {
        if ($byteCount < 0) {
            throw new Exception\LengthException('The byte count must not be negative');
        }
        if ($this->getRemainingBufferByteCount() >= $byteCount) {
            return;
        }
        $maxByteCount = $this->getRemainingByteCount();
        if ($maxByteCount !== null) {
            if (($maxByteCount < 0 && $byteCount > 0 || $maxByteCount < $byteCount) && !$allowIncomplete) {
                throw new Exception\UnderflowException('The source doesn\'t have enough remaining data to fulfill the request');
            }
            $byteCount = min($byteCount, $maxByteCount);
            if (!$skipping) {
                $this->ensureRemainingBufferByteCount($byteCount, $allowIncomplete);
            }
        } elseif (!$skipping) {
            if (!$this->ensureRemainingBufferByteCount($byteCount, $allowIncomplete) && !$allowIncomplete) {
                throw new Exception\UnderflowException('The source doesn\'t have enough remaining data to fulfill the request');
            }
            $byteCount = min($byteCount, $this->getRemainingBufferByteCount());
        }
    }

    /** {@inheritdoc} */
    public function read($byteCount, $allowIncomplete = false)
    {
        $this->checkByteCount($byteCount, $allowIncomplete, false);

        return self::readFromBuffers($byteCount, $this->firstBuffer, $this->cursor);
    }

    /** {@inheritdoc} */
    public function peek($byteCount, $allowIncomplete = false)
    {
        $this->checkByteCount($byteCount, $allowIncomplete, false);
        $firstBuffer = $this->firstBuffer;
        $cursor = $this->cursor;

        return self::readFromBuffers($byteCount, $firstBuffer, $cursor);
    }

    /** {@inheritdoc} */
    public function skip($byteCount, $allowIncomplete = false)
    {
        if ($this->stateCount) {
            $this->checkByteCount($byteCount, $allowIncomplete, false);
            $effectiveByteCount = $byteCount;
            while ($byteCount > 0) {
                if (self::readFromSingleBuffer($byteCount, $this->firstBuffer, $this->cursor) === false) {
                    break;
                }
            }

            return $effectiveByteCount - $byteCount;
        } else {
            $this->checkByteCount($byteCount, $allowIncomplete, true);
            $effectiveByteCount = $byteCount;
            while ($byteCount > 0) {
                $data = self::readFromSingleBuffer($byteCount, $this->firstBuffer, $this->cursor);
                if ($data === false) {
                    break;
                }
            }
            if ($byteCount > 0 && (!$allowIncomplete || !$this->source->wouldBlock($byteCount, $allowIncomplete))) {
                $cursor = $this->getConsumedByteCount();
                $skippedByteCount = $this->source->skip($byteCount, $allowIncomplete);
                $this->firstBuffer = new BufferedSourceBuffer($cursor + $skippedByteCount);
                $this->lastBuffer = $this->firstBuffer;
                $this->cursor = 0;
                if ($skippedByteCount < $byteCount && !$allowIncomplete) {
                    throw new Exception\UnderflowException('The source doesn\'t have enough remaining data to fulfill the request');
                }

                return $effectiveByteCount + $skippedByteCount - $byteCount;
            } else {
                return $effectiveByteCount - $byteCount;
            }
        }
    }
}
