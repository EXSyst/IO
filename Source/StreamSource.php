<?php

namespace EXSyst\Component\IO\Source;

use EXSyst\Component\IO\Exception;
use EXSyst\Component\IO\SelectableInterface;
use EXSyst\Component\IO\Sink\SinkInterface;
use EXSyst\Component\IO\Source;
use EXSyst\Component\IO\Source\Internal\StreamSourceState;

class StreamSource implements SelectableInterface, SinkInterface, SourceInterface
{
    /**
     * @var resource
     */
    private $stream;
    /**
     * @var bool
     */
    private $streamOwner;
    /**
     * @var callable|null
     */
    private $onClose;
    /**
     * @var bool
     */
    private $seekable;
    /**
     * @var int
     */
    private $baseCursor;

    public function __construct($stream, $streamOwner = false, $onClose = null)
    {
        if (!is_resource($stream)) {
            throw new Exception\InvalidArgumentException('The stream must be a resource');
        }
        if ($onClose !== null && !is_callable($onClose)) {
            throw new Exception\InvalidArgumentException('The on-close function must be callable');
        }
        $resType = get_resource_type($stream);
        if ($resType != 'stream' && $resType != 'file') {
            throw new Exception\InvalidArgumentException('The stream must be a suitable resource');
        }
        $this->stream = $stream;
        $this->streamOwner = $streamOwner;
        $this->onClose = $onClose;
        $this->seekable = ($stat = fstat($stream)) !== false && isset($stat['mode']) && ($stat['mode'] & 0170000) == 0 && fseek($stream, 0, SEEK_CUR) === 0;
        $this->baseCursor = ftell($stream);
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if ($this->streamOwner) {
            $this->streamOwner = false;
            fclose($this->stream);
            if ($this->onClose !== null) {
                call_user_func($this->onClose);
            }
        }
    }

    public function getStream()
    {
        return $this->stream;
    }

    public function isStreamOwner()
    {
        return $this->streamOwner;
    }

    public function isSeekable()
    {
        return $this->seekable;
    }

    /** {@inheritdoc} */
    public function getConsumedByteCount()
    {
        $cursor = ftell($this->stream);
        if ($cursor === false) {
            return;
        }

        return $cursor - $this->baseCursor;
    }

    /** {@inheritdoc} */
    public function getRemainingByteCount()
    {
        if (!$this->seekable) {
            return;
        }
        $cursor = ftell($this->stream);
        if ($cursor === false) {
            return;
        }
        $stat = fstat($this->stream);

        return ($stat !== false && isset($stat['size'])) ? ($stat['size'] - $cursor) : null;
    }

    /** {@inheritdoc} */
    public function getWrittenByteCount()
    {
        return $this->getConsumedByteCount();
    }

    private function selectRead()
    {
        $read = [$this->stream];
        $write = [];
        $except = [];

        return !!stream_select($read, $write, $except, 0);
    }

    /** {@inheritdoc} */
    public function isFullyConsumed()
    {
        if ($this->seekable) {
            $remain = $this->getRemainingByteCount();

            return $remain !== null && $remain <= 0;
        } else {
            if (!$this->selectRead()) {
                return false;
            }

            return feof($this->stream);
        }
    }

    /** {@inheritdoc} */
    public function wouldBlock($byteCount, $allowIncomplete = false)
    {
        if ($this->seekable) {
            return false;
        }
        if ($byteCount > 1 && !$allowIncomplete) {
            return true;
        } // Does PHP give any way to query how many bytes can be read without blocking ?
        return !$this->selectRead();
    }

    /** {@inheritdoc} */
    public function getBlockByteCount()
    {
        $stat = fstat($this->stream);

        return ($stat !== false && isset($stat['blksize'])) ? $stat['blksize'] : 1;
    }

    /** {@inheritdoc} */
    public function getBlockRemainingByteCount()
    {
        $blksize = $this->getBlockByteCount();
        $cursor = ftell($this->stream);
        if ($cursor === false) {
            return $blksize;
        }

        return $blksize - ($cursor % $blksize);
    }

    /** {@inheritdoc} */
    public function captureState()
    {
        if (!$this->seekable) {
            throw new Exception\LogicException('The stream is not seekable');
        }

        return new StreamSourceState($this->stream);
    }

    private function checkByteCount(&$byteCount, $allowIncomplete)
    {
        if ($byteCount < 0) {
            throw new Exception\LengthException('The byte count must not be negative');
        }
        $maxByteCount = $this->getRemainingByteCount();
        if ($maxByteCount !== null) {
            if (($maxByteCount < 0 && $byteCount > 0 || $maxByteCount < $byteCount) && !$allowIncomplete) {
                throw new Exception\UnderflowException('The source doesn\'t have enough remaining data to fulfill the request');
            }
            $byteCount = min($byteCount, $maxByteCount);

            return true;
        } else {
            return false;
        }
    }

    /** {@inheritdoc} */
    public function read($byteCount, $allowIncomplete = false)
    {
        if ($this->checkByteCount($byteCount, $allowIncomplete)) {
            return fread($this->stream, $byteCount);
        } else {
            $blocks = [];
            $blksize = max(Source::MIN_BLOCK_BYTE_COUNT, $this->getBlockByteCount());
            while ($byteCount > 0) {
                if ($allowIncomplete && !empty($blocks) && !$this->selectRead()) {
                    break;
                }
                $block = fread($this->stream, min($blksize, $byteCount));
                if ($block === false) {
                    throw new Exception\RuntimeException('An I/O error occurred');
                }
                if (empty($block)) {
                    break;
                }
                $byteCount -= strlen($block);
                $blocks[] = $block;
            }
            if ($byteCount > 0 && !$allowIncomplete) {
                throw new Exception\UnderflowException('The source doesn\'t have enough remaining data to fulfill the request');
            }

            return implode($blocks);
        }
    }

    /** {@inheritdoc} */
    public function peek($byteCount, $allowIncomplete = false)
    {
        $state = $this->captureState();
        try {
            return $this->read($byteCount, $allowIncomplete);
        } finally {
            $state->restore();
        }
    }

    /** {@inheritdoc} */
    public function skip($byteCount, $allowIncomplete = false)
    {
        if ($this->checkByteCount($byteCount, $allowIncomplete)) {
            fseek($this->stream, $byteCount, SEEK_CUR);

            return $byteCount;
        } else {
            $blksize = max(Source::MIN_BLOCK_BYTE_COUNT, $this->getBlockByteCount());
            $baseByteCount = $byteCount;
            while ($byteCount > 0) {
                if ($allowIncomplete && $byteCount < $baseByteCount && !$this->selectRead()) {
                    break;
                }
                $block = fread($this->stream, min($blksize, $byteCount));
                if ($block === false) {
                    throw new Exception\RuntimeException('An I/O error occurred');
                }
                if (empty($block)) {
                    break;
                }
                $byteCount -= strlen($block);
            }
            if ($byteCount > 0 && !$allowIncomplete) {
                throw new Exception\UnderflowException('The source doesn\'t have enough remaining data to fulfill the request');
            }

            return $baseByteCount - $byteCount;
        }
    }

    /** {@inheritdoc} */
    public function write($data)
    {
        $len = strlen($data);
        if ($len > 0) {
            for (;;) {
                $n = fwrite($this->stream, $data);
                if ($n === false) {
                    throw new Exception\RuntimeException('An I/O error occurred');
                }
                if (!$n) {
                    throw new Exception\OverflowException('The sink is full');
                }
                if ($n == $len) {
                    break;
                }
                $data = substr($data, $n);
                $len -= $n;
            }
        }

        return $this;
    }

    /** {@inheritdoc} */
    public function flush()
    {
        if (!fflush($this->stream)) {
            throw new Exception\RuntimeException('An I/O error occurred');
        }

        return $this;
    }

    public function __toString()
    {
        $meta = stream_get_meta_data($this->stream);

        return $meta['uri'];
    }
}
