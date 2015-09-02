<?php

namespace EXSyst\Component\IO;

/**
 * Encapsulates a resource (typically a stream) which can be passed to {@link http://php.net/stream_select stream_select}.
 *
 * @author Nicolas "Exter-N" L. <exter-n@exter-n.fr>
 *
 * @api
 */
interface SelectableInterface
{
    /**
     * Gets the encapsulated resource (typically a stream).
     *
     * @returns resource The encapsulated resource (typically a stream).
     */
    public function getStream();
}
