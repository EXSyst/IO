<?php

namespace EXSyst\Component\IO\Sink;

class StringSink implements SinkInterface
{
    const MAX_CONCAT_LENGTH = 4096;

    /**
     * @var array
     */
    private $data;
    /**
     * @var int
     */
    private $length;

    public function __construct()
    {
        $this->data = [];
        $this->length = 0;
    }

    /** {@inheritdoc} */
    public function getWrittenByteCount()
    {
        return $this->length;
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
        $data = strval($data);
        $len = strlen($data);
        if ($len > 0) {
            $ndata = count($this->data);
            if ($ndata > 0 && strlen($this->data[$ndata - 1]) + $len <= self::MAX_CONCAT_LENGTH) {
                $this->data[$ndata - 1] .= $data;
            } else {
                $this->data[] = $data;
            }
            $this->length += $len;
        }

        return $this;
    }

    /** {@inheritdoc} */
    public function flush()
    {
        return $this;
    }

    public function __toString()
    {
        return implode($this->data);
    }
}
