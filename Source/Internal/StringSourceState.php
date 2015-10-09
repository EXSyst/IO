<?php

/*
 * This file is part of the IO package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Component\IO\Source\Internal;

use EXSyst\Component\IO\Exception;
use EXSyst\Component\IO\StateInterface;

/**
 * Represents the state in which a string source was at a previous time.
 *
 * This class is for internal use.
 *
 * @author Nicolas "Exter-N" L. <exter-n@exter-n.fr>
 */
class StringSourceState implements StateInterface
{
    /**
     * @var int
     */
    private $offsetRef;
    /**
     * @var int
     */
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
            throw new Exception\LogicException('The source has already been restored to an earlier state');
        }
        $this->offsetRef = $this->offset;

        return $this;
    }
}
