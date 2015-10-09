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
     * @return resource The encapsulated resource (typically a stream).
     */
    public function getStream();
}
