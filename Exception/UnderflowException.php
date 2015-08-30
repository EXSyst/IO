<?php

namespace EXSyst\Component\IO\Exception;

/**
 * Exception thrown when performing an invalid operation on an empty container, such as removing an element.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class UnderflowException extends \UnderflowException implements ExceptionInterface
{
}
