<?php

namespace EXSyst\Component\IO\Source\Internal;

use LogicException;
use EXSyst\Component\IO\StateInterface;

/**
 * Represents the state in which a stream source was at a previous time.
 *
 * This class is for internal use.
 *
 * @author Nicolas "Exter-N" L. <exter-n@exter-n.fr>
 */
class StreamSourceState extends StateInterface
{
    private $stream;
    private $cursor;

    /**
     * Constructor.
     *
     * @param resource $stream
     */
    public function __construct($stream)
    {
        $this->stream = $stream;
        $this->cursor = ftell($stream);
    }

    /** {@inheritdoc} */
    public function restore()
    {
        if (ftell($this->stream) < $this->cursor) {
            throw new LogicException('The source has already been restored to an earlier state');
        }
        fseek($this->stream, $this->cursor, SEEK_SET);

        return $this;
    }
}
