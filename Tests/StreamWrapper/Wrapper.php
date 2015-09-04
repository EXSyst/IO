<?php

namespace EXSyst\Component\IO\Tests\StreamWrapper;

class Wrapper
{
    const WRAPPER_NAME = 'test';

    public $context;

    private static $_isRegistered = false;

    public static function getContext(array $context)
    {
        if (!self::$_isRegistered) {
            stream_wrapper_register(self::WRAPPER_NAME, get_class());
            self::$_isRegistered = true;
        }

        return stream_context_create([self::WRAPPER_NAME => $context]);
    }

    public function stream_open()
    {
        $context = stream_context_get_options($this->context);
        if (isset($context['open'])) {
            call_user_func($context['fopen']);
        }

        return true;
    }

    public function stream_stat()
    {
        $context = stream_context_get_options($this->context)[self::WRAPPER_NAME];
        if (isset($context['stat'])) {
            return call_user_func($context['stat']);
        }

        return true;
    }

    public function stream_seek($offset, $whence = SEEK_SET)
    {
        $context = stream_context_get_options($this->context)[self::WRAPPER_NAME];
        if (isset($context['seek'])) {
            return call_user_func($context['seek']);
        }
    }

    // public function stream_tell()
    // {
    //     $context = stream_context_get_options($this->context)[self::WRAPPER_NAME];
    //     if (isset($context['tell'])) {
    //         return call_user_func($context['tell']);
    //     }
    // }
}
