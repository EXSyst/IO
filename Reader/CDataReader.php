<?php

namespace EXSyst\Component\IO\Reader;

use EXSyst\Component\IO\Exception;
use EXSyst\Component\IO\Source;
use EXSyst\Component\IO\Source\SourceInterface;
use EXSyst\Component\IO\Sink\StringSink;
use EXSyst\Component\IO\Source\OuterSource;
use EXSyst\Component\IO\Source\StringSource;

class CDataReader extends OuterSource
{
    const WHITE_SPACE_MASK = "\011\n\013\014\r ";

    /**
     * @param SourceInterface
     *
     * @return CDataReader
     */
    public static function fromSource(SourceInterface $source)
    {
        if ($source instanceof self) {
            return $source;
        } elseif ($source instanceof StringSource) {
            return new StringCDataReader($source);
        } else {
            return new self($source);
        }
    }

    /**
     * @param string   $string
     * @param int      $start
     * @param int|null $end
     *
     * @return StringCDataReader
     */
    public static function fromString($string, $start = 0, $end = null)
    {
        return new StringCDataReader(new StringSource($string, $start, $end));
    }

    /**
     * @param string $string
     *
     * @return bool
     */
    public function eat($string)
    {
        $string = strval($string);
        $len = strlen($string);
        $src = $this->source;
        try {
            $data = $src->peek($len);
        } catch (Exception\UnderflowException $e) {
            return false;
        }
        if ($data != $string) {
            return false;
        }
        $src->skip($len);

        return true;
    }

    /**
     * @param string $string
     *
     * @return string|null
     */
    public function eatCaseInsensitive($string)
    {
        $string = strval($string);
        $len = strlen($string);
        $src = $this->source;
        try {
            $data = $src->peek($len);
        } catch (Exception\UnderflowException $e) {
            return;
        }
        if (strcasecmp($data, $string)) {
            return;
        }
        $src->skip($len);

        return $data;
    }

    /**
     * @param string[]|\Traversable $strings
     * @param bool                  $caseInsensitive
     *
     * @return string|null
     */
    public function eatAny($strings, $caseInsensitive = false)
    {
        if ($caseInsensitive) {
            foreach ($strings as $string) {
                if (($string2 = $this->eatCaseInsensitive($string)) !== null) {
                    return $string2;
                }
            }
        } else {
            foreach ($strings as $string) {
                if ($this->eat($string)) {
                    return $string;
                }
            }
        }
    }

    /**
     * @param string   $mask
     * @param int|null $length
     * @param bool     $allowIncomplete
     *
     * @return string
     */
    public function eatSpan($mask, $length = null, $allowIncomplete = false)
    {
        $src = $this->source;
        $blksize = max(Source::MIN_SPAN_BLOCK_BYTE_COUNT, $src->getBlockByteCount());
        $sink = new StringSink();
        while (!isset($length) || $length > 0) {
            if ($allowIncomplete && $sink->getWrittenByteCount() > 0 && $src->wouldBlock(isset($length) ? min($length, $blksize) : $blksize, true)) {
                break;
            }
            $data = $src->peek(isset($length) ? min($length, $blksize) : $blksize, true);
            if (empty($data)) {
                break;
            }
            $len = strspn($data, $mask);
            if (!$len) {
                break;
            }
            $src->skip($len);
            if ($len < strlen($data)) {
                $sink->write(substr($data, 0, $len));
                break;
            } else {
                $sink->write($data);
            }
            if (isset($length)) {
                $length -= $len;
            }
        }

        return strval($sink);
    }

    /**
     * @param string   $mask
     * @param int|null $length
     * @param bool     $allowIncomplete
     *
     * @return string
     */
    public function eatCSpan($mask, $length = null, $allowIncomplete = false)
    {
        $src = $this->source;
        $blksize = max(Source::MIN_SPAN_BLOCK_BYTE_COUNT, $src->getBlockByteCount());
        $sink = new StringSink();
        while (!isset($length) || $length > 0) {
            if ($allowIncomplete && $sink->getWrittenByteCount() > 0 && $src->wouldBlock(isset($length) ? min($length, $blksize) : $blksize, true)) {
                break;
            }
            $data = $src->peek(isset($length) ? min($length, $blksize) : $blksize, true);
            if (empty($data)) {
                break;
            }
            $len = strcspn($data, $mask);
            if (!$len) {
                break;
            }
            $src->skip($len);
            if ($len < strlen($data)) {
                $sink->write(substr($data, 0, $len));
                break;
            } else {
                $sink->write($data);
            }
            if (isset($length)) {
                $length -= $len;
            }
        }

        return strval($sink);
    }

    /**
     * @param int|null $length
     * @param bool     $allowIncomplete
     *
     * @return int
     */
    public function eatWhiteSpace($length = null, $allowIncomplete = false)
    {
        return strlen($this->eatSpan(self::WHITE_SPACE_MASK, $length, $allowIncomplete));
    }

    /**
     * @return string
     */
    public function eatToFullConsumption()
    {
        $sink = new StringSink();
        Source::pipe($this->source, $sink);

        return strval($sink);
    }
}
