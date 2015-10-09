<?php

/*
 * This file is part of the IO package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Component\IO\Tests\StreamWrapper;

class WrapperHandler
{
    public static function open(array $options, $mode = 'r+')
    {
        return fopen('test://', $mode, false, Wrapper::getContext($options));
    }
}
