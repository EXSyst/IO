<?php

namespace EXSyst\Component\IO\Tests\StreamWrapper;

class WrapperHandler
{
    public static function open(array $options, $mode = 'r+')
    {
        return fopen('test://', $mode, false, Wrapper::getContext($options));
    }
}
