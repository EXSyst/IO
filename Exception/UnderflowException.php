<?php

/*
 * This file is part of the IO package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Component\IO\Exception;

/**
 * Exception thrown when performing an invalid operation on an empty container, such as removing an element.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class UnderflowException extends \UnderflowException implements ExceptionInterface
{
}
