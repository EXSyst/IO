<?php

namespace EXSyst\Component\IO\Reader;

use EXSyst\Component\IO\Exception;
use EXSyst\Component\IO\Source\OuterSource;

class SerializedReader extends OuterSource
{
    public function __construct(CDataReader $source)
    {
        parent::__construct($source);
    }

    public function readValue()
    {
        $serialized = $this->readSerializedValue();
        if ($serialized == 'b:0;') {
            return false;
        }
        $value = unserialize($serialized);
        if ($value === false) {
            throw new Exception\RuntimeException('Invalid serialized data');
        }

        return $value;
    }

    public function readSerializedValue()
    {
        $parts = [$this->source->eatCSpan(':;')];
        while (($sep = $this->source->read(1)) == ':') {
            $head = $this->source->read(1);
            if ($head == '"') {
                $parts[] = $head.$this->source->read(intval(end($parts)) + 1);
            } elseif ($head == '{') {
                $subs = [];
                while ($this->source->peek(1) != '}') {
                    $subs[] = $this->readSerializedValue();
                }
                $parts[] = $head.implode($subs).$this->source->read(1);

                return implode(':', $parts);
            } else {
                $parts[] = $head.$this->source->eatCSpan(':;');
            }
        }

        return implode(':', $parts).$sep;
    }
}
