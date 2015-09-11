<?php

namespace EXSyst\Component\IO\Sink;

class RecordFunctionSink implements SinkInterface
{
    /**
     * @var int
     */
    private $written;
    /**
     * @var string
     */
    private $buffer;
    /**
     * @var callable
     */
    private $recordFunction;
    /**
     * @var string|null
     */
    private $recordSeparator;
    /**
     * @var int|null
     */
    private $recordSize;

    /**
     * Constructor.
     *
     * @param callable $recordFunction
     * @param mixed    $recordSeparator
     * @param int|null $recordSize
     */
    public function __construct($recordFunction, $recordSeparator = PHP_EOL, $recordSize = null)
    {
        $this->written = 0;
        $this->buffer = '';
        $this->recordFunction = $recordFunction;
        $this->recordSeparator = ($recordSeparator === null) ? null : strval($recordSeparator);
        $this->recordSize = ($recordSize === null) ? null : intval($recordSize);
    }

    public function __destruct()
    {
        if (!empty($this->buffer)) {
            call_user_func($this->recordFunction, $this->buffer);
            $this->buffer = '';
        }
    }

    /**
     * @return callable
     */
    public function getRecordFunction()
    {
        return $this->recordFunction;
    }

    /**
     * @return string|null
     */
    public function getRecordSeparator()
    {
        return $this->recordSeparator;
    }

    /**
     * @return int|null
     */
    public function getRecordSize()
    {
        return $this->recordSize;
    }

    /** {@inheritdoc} */
    public function getWrittenByteCount()
    {
        return $this->written;
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
    public function write($data)
    {
        $this->buffer .= $data;
        $this->written += strlen($data);
        if ($this->recordSeparator !== null) {
            $pos = 0;
            $rsl = strlen($this->recordSeparator);
            while (($pos2 = strpos($this->buffer, $this->recordSeparator, $pos)) !== false) {
                if ($this->recordSize !== null && $pos2 - $pos > $this->recordSize) {
                    for ($i = $pos; $i < $pos2; $i += $this->recordSize) {
                        call_user_func($this->recordFunction, substr($this->buffer, $i, min($this->recordSize, $pos2 - $i)));
                    }
                } else {
                    call_user_func($this->recordFunction, substr($this->buffer, $pos, $pos2 - $pos));
                }
                $pos = $pos2 + $rsl;
            }
            if ($pos > 0) {
                $this->buffer = substr($this->buffer, $pos);
            }
        }
        if ($this->recordSize !== null && ($len = strlen($this->buffer)) > $this->recordSize) {
            for ($pos = 0; $pos < $len - $this->recordSize; $pos += $this->recordSize) {
                call_user_func($this->recordFunction, substr($this->buffer, $pos, $this->recordSize));
            }
            $this->buffer = substr($this->buffer, $pos);
        }

        return $this;
    }

    /** {@inheritdoc} */
    public function flush()
    {
        return $this;
    }
}
