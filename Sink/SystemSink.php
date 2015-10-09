<?php

/*
 * This file is part of the IO package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Component\IO\Sink;

final class SystemSink implements SinkInterface
{
    /**
     * @var int
     */
    const BLOCK_BYTE_COUNT = 1024;

    /**
     * @var int
     */
    public $written;

    /**
     * @var SystemSink
     */
    private static $instance;

    private function __construct()
    {
        $this->written = 0;
    }

    /**
     * @return self
     */
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
        $this->written += strlen($data);

        return $this;
    }

    /** {@inheritdoc} */
    public function flush()
    {
        flush();

        return $this;
    }
}
