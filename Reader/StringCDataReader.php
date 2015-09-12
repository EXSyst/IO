<?php

namespace EXSyst\Component\IO\Reader;

use EXSyst\Component\IO\Source\StringSource;

class StringCDataReader extends CDataReader
{
    /**
     * @param StringSource $source
     */
    public function __construct(StringSource $source)
    {
        parent::__construct($source);
    }

    /** {@inheritdoc} */
    public function eat($string)
    {
        $string = strval($string);
        $len = strlen($string);
        $src = $this->source;
        if ($src->end - $src->offset < $len) {
            return false;
        }
        if (substr_compare($src->data, $string, $src->offset, $len) != 0) {
            return false;
        }
        $src->offset += $len;

        return true;
    }

    /** {@inheritdoc} */
    public function eatCaseInsensitive($string)
    {
        $string = strval($string);
        $len = strlen($string);
        $src = $this->source;
        if ($src->end - $src->offset < $len) {
            return;
        }
        if (substr_compare($src->data, $string, $src->offset, $len, true) != 0) {
            return;
        }
        $offset = $src->offset;
        $src->offset += $len;

        return substr($src->data, $offset, $len);
    }

    /** {@inheritdoc} */
    public function eatSpan($mask, $maxLength = null, $onlyNonBlocking = false)
    {
        $src = $this->source;
        $dataLength = $src->end - $src->offset;
        $maxLength = ($maxLength === null) ? $dataLength : min($maxLength, $dataLength);
        $maxLength = strspn($src->data, $mask, $src->offset, $maxLength);
        $substr = substr($src->data, $src->offset, $maxLength);
        $src->offset += $maxLength;

        return $substr;
    }

    /** {@inheritdoc} */
    public function eatCSpan($mask, $maxLength = null, $onlyNonBlocking = false)
    {
        $src = $this->source;
        $dataLength = $src->end - $src->offset;
        $maxLength = ($maxLength === null) ? $dataLength : min($maxLength, $dataLength);
        $maxLength = strcspn($src->data, $mask, $src->offset, $maxLength);
        $substr = substr($src->data, $src->offset, $maxLength);
        $src->offset += $maxLength;

        return $substr;
    }

    /** {@inheritdoc} */
    public function eatToFullConsumption()
    {
        $src = $this->source;
        $byteCount = $src->end - $src->offset;
        if ($byteCount <= 0) {
            return '';
        }
        $str = substr($src->data, $src->offset, $byteCount);
        $src->offset = $src->end;

        return $str;
    }

    /**
     * @param string $pcrePattern
     * @param int    $flags
     *
     * @return array|null
     */
    public function eatRegex($pcrePattern, $flags = 0)
    {
        $src = $this->source;
        if (!preg_match($pcrePattern, $src->data, $matches, $flags | PREG_OFFSET_CAPTURE, $src->offset)) {
            return;
        }
        $offset = $src->offset;
        if ($matches[0][1] != $offset) {
            return;
        }
        $length = strlen($matches[0][0]);
        if ($offset + $length > $src->end) {
            if (!preg_match($pcrePattern, substr($src->data, 0, $src->end), $matches, $flags | PREG_OFFSET_CAPTURE, $src->offset)) {
                return;
            }
            $offset = $src->offset;
            if ($matches[0][1] != $offset) {
                return;
            }
            $length = strlen($matches[0][0]);
        }
        $src->offset += $length;
        if (($flags & PREG_OFFSET_CAPTURE) != 0) {
            foreach ($matches as &$match) {
                $match[1] -= $offset;
            }
        } else {
            foreach ($matches as &$match) {
                $match = $match[0];
            }
        }

        return $matches;
    }

    /**
     * @return string This object's string representation
     */
    public function __toString()
    {
        return $this->source->data;
    }
}
