<?php

namespace EXSyst\Component\IO\Reader;

use EXSyst\Component\IO\Exception;
use EXSyst\Component\IO\Source\OuterSource;

class JsonReader extends OuterSource
{
    public function __construct(CDataReader $source)
    {
        parent::__construct($source);
    }

    public function readValue($assoc = false, $depth = 512, $options = 0)
    {
        $json = $this->readJsonValue($depth);
        if ($json == 'null') {
            return;
        }
        $value = json_decode($json, $assoc, $depth, $options);
        if ($value === null) {
            throw new Exception\RuntimeException('Invalid JSON data');
        }

        return $value;
    }

    /**
     * @return string
     */
    public function readJsonValue($depth = 512, $inner = false)
    {
        $this->source->eatWhiteSpace();
        if (!$inner && $this->source->isFullyConsumed()) {
            throw new Exception\UnderflowException('The source doesn\'t have enough remaining data to fulfill the request');
        }
        try {
            $num = $this->source->eatSpan('+-.0123456789Ee');
            if (strlen($num)) {
                return $num;
            }
            if ($this->source->eat('"')) {
                $parts = ['"'];
                for (;;) {
                    $parts[] = $this->source->eatCSpan('"\\');
                    if ($this->source->eat('\\')) {
                        $parts[] = '\\'.$this->source->read(1);
                    } else {
                        if ($this->source->read(1) != '"') {
                            throw new Exception\RuntimeException('Invalid JSON data');
                        }
                        $parts[] = '"';
                        break;
                    }
                }

                return implode($parts);
            }
            if ($this->source->eat('[')) {
                if ($depth < 2) {
                    throw new Exception\RuntimeException('Too deeply nested JSON data');
                }
                $this->source->eatWhiteSpace();
                if ($this->source->eat(']')) {
                    return '[]';
                }
                $subs = [];
                do {
                    $subs[] = $this->readJsonValue($depth - 1, true);
                } while ($this->source->eat(','));
                if ($this->source->read(1) != ']') {
                    throw new Exception\RuntimeException('Invalid JSON data');
                }

                return '['.implode(',', $subs).']';
            }
            if ($this->source->eat('{')) {
                if ($depth < 2) {
                    throw new Exception\RuntimeException('Too deeply nested JSON data');
                }
                $this->source->eatWhiteSpace();
                if ($this->source->eat('}')) {
                    return '{}';
                }
                $subs = [];
                do {
                    $key = $this->readJsonValue($depth - 1, true);
                    if ($this->source->read(1) != ':') {
                        throw new Exception\RuntimeException('Invalid JSON data');
                    }
                    $subs[] = $key.':'.$this->readJsonValue($depth - 1, true);
                } while ($this->source->eat(','));
                if ($this->source->read(1) != '}') {
                    throw new Exception\RuntimeException('Invalid JSON data');
                }

                return '{'.implode(',', $subs).'}';
            }
            $kw = $this->source->eatAny(['null', 'true', 'false']);
            if ($kw !== null) {
                return $kw;
            }
            throw new Exception\RuntimeException('Invalid JSON data');
        } finally {
            $this->source->eatWhiteSpace(null, !$inner);
        }
    }
}
