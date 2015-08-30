<?php

namespace EXSyst\Component\IO\Sink;

use EXSyst\Component\IO\SinkInterface;

final class SystemSink implements SinkInterface
{
    const BLOCK_BYTE_COUNT = 1024;

    /**
     * @var int
     */
    private $written;

    /**
     * @var SystemSink
     */
    private static $instance;

    private function __construct()
    {
        $this->written = 0;
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /** {@inheritdoc} */
    public function getWrittenByteCount()
    {
        return $this->written;
    }

    /** {@inheritdoc} */
    public function getBlockByteCount()
    {
        return self::BLOCK_BYTE_COUNT;
    }

    /** {@inheritdoc} */
    public function getBlockRemainingByteCount()
    {
        return self::BLOCK_BYTE_COUNT - ($this->written % self::BLOCK_BYTE_COUNT);
    }

    /** {@inheritdoc} */
    public function write($data)
    {
        echo $data;

        return $this;
    }

    /** {@inheritdoc} */
    public function flush()
    {
        flush();

        return $this;
    }
}
