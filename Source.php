<?php

namespace EXSyst\Component\IO;

use EXSyst\Component\IO\Reader\CDataReader;
use EXSyst\Component\IO\Source\BufferedSource;
use EXSyst\Component\IO\Source\StreamSource;
use EXSyst\Component\IO\Source\StringSource;

final class Source
{
    const MIN_BLOCK_BYTE_COUNT = 4096;
    const MIN_SPAN_BLOCK_BYTE_COUNT = 128;

    private function __construct()
    {
    }

    public static function fromString($string, $start = 0, $end = null)
    {
        return new StringSource($string, $start, $end);
    }

    public static function fromStream($stream, $streamOwner = false, $onClose = null, $buffered = true)
    {
        try {
            $src = new StreamSource($stream, $streamOwner, $onClose);
        } catch (Exception\ExceptionInterface $e) {
            if ($streamOwner) {
                fclose($stream);
                if ($onClose !== null) {
                    call_user_func($onClose);
                }
            }
            throw $e;
        }
        if ($buffered) {
            $src = new BufferedSource($src);
        }

        return $src;
    }

    public static function fromFile($file, $mode = 'rb', $onClose = null, $buffered = true)
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new Exception\ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
        try {
            $fd = fopen($file, $mode);
        } finally {
            restore_error_handler();
        }

        return self::fromStream($fd, true, $onClose, $buffered);
    }

    public static function fromInput()
    {
        return (PHP_SAPI == 'cli') ? self::fromStream(STDIN) : self::fromFile('php://input');
    }

    public static function transact(SourceInterface $source, $transactionFn, $restoreOnFalsyReturn = false)
    {
        if (!is_callable($transactionFn)) {
            throw new Exception\InvalidArgumentException('The transaction function must be callable');
        }
        $state = $source->captureState();
        try {
            $retval = call_user_func($transactionFn);
        } catch (\Exception $ex) {
            $state->restore();
            throw $ex;
        }
        if ($restoreOnFalsyReturn && !$retval) {
            $state->restore();
        }

        return $retval;
    }

    public static function pipe(SourceInterface $source, SinkInterface $sink)
    {
        $blksize = max($source->getBlockByteCount(), self::MIN_BLOCK_BYTE_COUNT);
        for (;;) {
            $data = $source->read($blksize, true);
            if (empty($data)) {
                break;
            }
            $sink->write($data);
        }
    }

    public static function getContents(SourceInterface $source)
    {
        return CDataReader::fromSource($source)->eatToFullConsumption();
    }
}
