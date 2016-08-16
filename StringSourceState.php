<?php

/*
 * This file is part of the IO package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Component\IO;

/**
 * Represents the state in which a string source was at a previous time.
 *
 * @author Nicolas "Exter-N" L. <exter-n@exter-n.fr>
 */
class StringSourceState
{
    private $offsetRef;
    private $offset;
    private $lineRef;
    private $line;
    private $rowRef;
    private $row;

    /**
     * @param int $offsetRef
     * @param int $lineRef
     * @param int $rowRef
     *
     * @internal Do not instantiate this class by hand.
     */
    public function __construct(&$offsetRef, &$lineRef, &$rowRef)
    {
        $this->offsetRef = &$offsetRef;
        $this->offset = $offsetRef;
        $this->lineRef = &$lineRef;
        $this->line = $lineRef;
        $this->rowRef = &$rowRef;
        $this->row = $rowRef;
    }

    /**
     * Restores the source to which this state belongs to this state.
     */
    public function restore()
    {
        $this->offsetRef = $this->offset;
        $this->lineRef = $this->line;
        $this->rowRef = $this->row;
    }
}
