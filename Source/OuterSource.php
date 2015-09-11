<?php

namespace EXSyst\Component\IO\Source;

/**
 * Helper class for decorating a source.
 *
 * @author Nicolas "Exter-N" L. <exter-n@exter-n.fr>
 *
 * @api
 */
abstract class OuterSource implements SourceInterface
{
    /**
     * @var SourceInterface The inner source
     *
     * @api
     */
    protected $source;

    /**
     * Constructor.
     *
     * @param SourceInterface $source The inner source
     *
     * @api
     */
    public function __construct(SourceInterface $source)
    {
        $this->source = $source;
    }

    /**
     * Gets the inner source.
     *
     * @return SourceInterface The inner source
     *
     * @api
     */
    public function getInnerSource()
    {
        return $this->source;
    }

    /**
     * Gets the outermost source which extends or implements a given type.
     *
     * @param string $type A type name
     *
     * @return SourceInterface|null The outermost source which extends or implements the given type, or null if there is none
     *
     * @api
     */
    public function getSourceByType($type)
    {
        $source = $this->source;
        while ($source instanceof self) {
            if ($source instanceof $type) {
                return $source;
            }
            $source = $source->source;
        }

        if ($source instanceof $type) {
            return $source;
        }
    }

    /**
     * Gets the innermost source.
     *
     * @return SourceInterface The innermost source
     *
     * @api
     */
    public function getInnermostSource()
    {
        $source = $this->source;
        while ($source instanceof self) {
            $source = $source->source;
        }

        return $source;
    }

    /** {@inheritdoc} */
    public function getConsumedByteCount()
    {
        return $this->source->getConsumedByteCount();
    }

    /** {@inheritdoc} */
    public function getRemainingByteCount()
    {
        return $this->source->getRemainingByteCount();
    }

    /** {@inheritdoc} */
    public function isFullyConsumed()
    {
        return $this->source->isFullyConsumed();
    }

    /** {@inheritdoc} */
    public function wouldBlock($byteCount, $allowIncomplete = false)
    {
        return $this->source->wouldBlock($byteCount, $allowIncomplete);
    }

    /** {@inheritdoc} */
    public function getBlockByteCount()
    {
        return $this->source->getBlockByteCount();
    }

    /** {@inheritdoc} */
    public function getBlockRemainingByteCount()
    {
        return $this->source->getBlockRemainingByteCount();
    }

    /** {@inheritdoc} */
    public function captureState()
    {
        return $this->source->captureState();
    }

    /** {@inheritdoc} */
    public function read($byteCount, $allowIncomplete = false)
    {
        return $this->source->read($byteCount, $allowIncomplete);
    }

    /** {@inheritdoc} */
    public function peek($byteCount, $allowIncomplete = false)
    {
        return $this->source->peek($byteCount, $allowIncomplete);
    }

    /** {@inheritdoc} */
    public function skip($bytecount, $allowIncomplete = false)
    {
        return $this->source->skip($bytecount, $allowIncomplete);
    }
}
