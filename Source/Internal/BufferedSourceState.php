<?php

namespace EXSyst\Component\IO\Source\Internal;

use EXSyst\Component\IO\Exception;
use EXSyst\Component\IO\StateInterface;

/**
 * Represents the state in which a buffered source was at a previous time.
 *
 * This class is for internal use.
 *
 * @author Nicolas "Exter-N" L. <exter-n@exter-n.fr>
 */
class BufferedSourceState extends StateInterface
{
    private $firstBufferRef;
    private $firstBuffer;
    private $cursorRef;
    private $cursor;
    private $stateCountRef;

    /**
     * Constructor.
     *
     * @param BufferedSourceBuffer $firstBufferRef
     * @param int                  $cursorRef
     * @param int                  $stateCountRef
     */
    public function __construct(BufferedSourceBuffer &$firstBufferRef, &$cursorRef, &$stateCountRef)
    {
        $this->firstBufferRef = &$firstBufferRef;
        $this->firstBuffer = $firstBufferRef;
        $this->cursorRef = &$cursorRef;
        $this->cursor = $cursorRef;
        $this->stateCountRef = &$stateCountRef;
        ++$this->stateCountRef;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        --$this->stateCountRef;
    }

    /** {@inheritdoc} */
    public function restore()
    {
        if ($this->firstBufferRef->offset < $this->firstBuffer->offset) {
            throw new Exception\LogicException('The source has already been restored to an earlier state');
        }
        if ($this->cursorRef < $this->cursor) {
            throw new Exception\LogicException('The source has already been restored to an earlier state');
        }
        $this->firstBufferRef = $this->firstBuffer;
        $this->cursorRef = $this->cursor;

        return $this;
    }
}
