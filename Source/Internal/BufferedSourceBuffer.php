<?php

namespace EXSyst\Component\IO\Source\Internal;

/**
 * A single buffer in a buffered source.
 *
 * This class is for internal use.
 *
 * @author Nicolas "Exter-N" L. <exter-n@exter-n.fr>
 */
class BufferedSourceBuffer
{
    public $offset = 0;
    public $data = '';
    public $length = 0;
    public $next = null;

    /**
     * Constructor.
     *
     * @param int $offset Offset of this buffer since the beginning of the source
     */
    public function __construct($offset = 0)
    {
        $this->offset = $offset;
    }
}
