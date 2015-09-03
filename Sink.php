<?php

namespace EXSyst\Component\IO;

use EXSyst\Component\IO\Sink\SinkInterface;
use EXSyst\Component\IO\Sink\RecordFunctionSink;
use EXSyst\Component\IO\Sink\SystemSink;
use EXSyst\Component\IO\Source\StreamSource;

final class Sink
{
    private function __construct()
    {
    }

    public static function fromStream($stream, $streamOwner = false, $onClose = null)
    {
        return Source::fromStream($stream, $streamOwner, $onClose, false);
    }

    public static function fromFile($file, $mode = 'wb', $onClose = null)
    {
        return Source::fromFile($file, $mode, $onClose, false);
    }

    public static function fromOutput()
    {
        return SystemSink::getInstance();
    }

    public static function fromError()
    {
        return self::fromStream(STDERR);
    }

    public static function fromLog($messageType = 0, $destination = null, $extraHeaders = null)
    {
        if ($extraHeaders !== null) {
            $fn = function ($line) use ($messageType, $destination, $extraHeaders) {
                error_log($line, $messageType, $destination, $extraHeaders);
            };
        } elseif ($destination !== null) {
            $fn = function ($line) use ($messageType, $destination) {
                error_log($line, $messageType, $destination);
            };
        } else {
            $fn = function ($line) use ($messageType) {
                error_log($line, $messageType);
            };
        }

        return new RecordFunctionSink($fn);
    }

    public static function writeLine(SinkInterface $sink, $data)
    {
        $sink->write($data.PHP_EOL);
    }

    public static function writeFormatted(SinkInterface $sink, $format)
    {
        $args = array_slice(func_get_args(), 2);
        if ($sink instanceof SystemSink) {
            $sink->written += vprintf($format, $args);
        } elseif ($sink instanceof StreamSource) {
            vfprintf($sink->getStream(), $format, $args);
        } else {
            $sink->write(vsprintf($format, $args));
        }
    }

    public static function varDump(SinkInterface $sink/*, ...$expressions */)
    {
        ob_start();
        call_user_func_array('var_dump', array_slice(func_get_args(), 1));
        $sink->write(ob_get_clean());
    }

    public static function varExport(SinkInterface $sink, $expression)
    {
        $sink->write(var_export($expression, true));
    }

    public static function printR(SinkInterface $sink, $expression)
    {
        $sink->write(print_r($expression, true));
    }
}
