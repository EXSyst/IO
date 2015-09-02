<?php

namespace EXSyst\Component\IO\Sink;

use EXSyst\Component\IO\Exception;

class TeeSink implements SinkInterface
{
    /**
     * @var array
     */
    private $sinks;

    /**
     * @var int
     */
    private $written;

    public function __construct(array $sinks)
    {
        foreach ($sinks as $sink) {
            if (!($sink instanceof SinkInterface)) {
                throw new Exception\InvalidArgumentException('The sub-sinks must be instanceof EXSyst\Component\IO\Sink\SinkInterface');
            }
        }
        $this->sinks = array_values($sinks);
        $this->written = 0;
    }

    public function getSinks()
    {
        return $this->sinks;
    }

    /** {@inheritdoc} */
    public function getWrittenByteCount()
    {
        return $this->written;
    }

    /** {@inheritdoc} */
    public function getBlockByteCount()
    {
        return array_reduce($this->sinks, function ($carry, $sink) {
            $blksize = $sink->getBlockByteCount();
            $a = $carry;
            $b = $blksize;
            while ($b != 0) { // Calc greateast common divisor
                $m = $a % $b;
                $a = $b;
                $b = $m;
            }

            return $carry * $blksize / $a; // least common multiple
        }, 1);
    }

    /** {@inheritdoc} */
    public function getBlockRemainingByteCount()
    {
        $blksize = $this->getBlockByteCount();

        return $blksize - ($this->written % $blksize);
    }

    /** {@inheritdoc} */
    public function write($data)
    {
        foreach ($this->sinks as $sink) {
            $sink->write($data);
        }
        $this->written += strlen($data);

        return $this;
    }

    /** {@inheritdoc} */
    public function flush()
    {
        foreach ($this->sinks as $sink) {
            $sink->flush();
        }

        return $this;
    }
}
