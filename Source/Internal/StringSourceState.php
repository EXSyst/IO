<?php

namespace EXSyst\Component\IO\Source\Internal;

use LogicException;
use EXSyst\Component\IO\StateInterface;

/**
 * Represents the state in which a string source was at a previous time.
 *
 * This class is for internal use.
 *
 * @author Nicolas "Exter-N" L. <exter-n@exter-n.fr>
 */
class StringSourceState extends StateInterface
{
    private $offsetRef;
    private $offset;

    /**
     * Constructor.
     *
     * @param int $offsetRef
     */
    public function __construct(&$offsetRef)
    {
        $this->offsetRef = &$offsetRef;
        $this->offset = $offsetRef;
    }

    /** {@inheritdoc} */
    public function restore()
    {
        if ($this->offsetRef < $this->offset) {
            throw new LogicException('The source has already been restored to an earlier state');
        }
        $this->offsetRef = $this->offset;

        return $this;
    }
}
