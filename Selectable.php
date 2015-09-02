<?php

namespace EXSyst\Component\IO;

use EXSyst\Component\IO\Source\OuterSource;

use React\EventLoop\LoopInterface;

/**
 * Encapsulates a resource (typically a stream) which can be passed to {@link http://php.net/stream_select stream_select}.
 *
 * Bare-bones implementation of {@see SelectableInterface}.
 *
 * @author Nicolas "Exter-N" L. <exter-n@exter-n.fr>
 *
 * @api
 */
class Selectable implements SelectableInterface
{
    /**
     * @var resource The encapsulated resource (typically a stream).
     */
    private $stream;

    /**
     * Constructor.
     *
     * @param resource $stream The encapsulated resource (typically a stream).
     */
    public function __construct($stream)
    {
        if (!is_resource($stream)) {
            throw new Exception\InvalidArgumentException('The stream must be a resource');
        }
        $this->stream = $stream;
    }

    /** {@inheritdoc} */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Extracts the wrapped stream from an object.
     *
     * @param OuterSource|SelectableInterface An object wrapping a stream
     *
     * @return resource|null The wrapped stream, or null if the object does not wrap a stream
     */
    public static function streamOf($object)
    {
        if ($object instanceof OuterSource) {
            $selectable = $object->getSourceByType(SelectableInterface::class);
        } elseif ($object instanceof SelectableInterface) {
            $selectable = $object;
        } else {
            $selectable = null;
        }

        if ($selectable !== null) {
            return $selectable->getStream();
        }
    }

    private static function preprocessSet(array &$objects, array &$objectsByStream, array &$streams)
    {
        foreach ($objects as $object) {
            $stream = self::streamOf($object);
            if ($stream === null) {
                throw new Exception\InvalidArgumentException('All the objects must be, or wrap, selectables');
            }
            $objectsByStream[intval($stream)] = $object;
            $streams[] = $object;
        }
    }

    /**
     * Watches for conditions in one or many streams.
     *
     * @param array $readObjects On call, set of objects wrapping streams which must be watched for incoming data. On return, the set will only contain streams which actually have incoming data.
     * @param array $writeObjects On call, set of objects wrapping streams which must be watched for ability to write to them. On return, the set will only contain streams which can actually be written to.
     * @param array $exceptObjects On call, set of objects wrapping streams which must be watched for incoming high-priority data. On return, the set will only contain streams which actually have incoming high-priority data.
     * @param float $seconds Maximum number of seconds to wait for at least one stream to fulfill the condition for which it is watched.
     *
     * @return int Number of streams which actually fulfill the condition for which they are watched
     *
     * @throws Exception\InvalidArgumentException If all the sets are empty or any set contains anything else than selectables
     * @throws Exception\RuntimeException If an I/O operation fails
     */
    public static function select(array &$readObjects, array &$writeObjects, array &$exceptObjects, $seconds = 0)
    {
        if (count($readObjects) == 0 && count($writeObjects) == 0 && count($exceptObjects) == 0) {
            throw new Exception\InvalidArgumentException('You must pass at least one object');
        }
        $readObjectsByStream = [ ];
        $readStreams = [ ];
        self::preprocessSet($readObjects, $readObjectsByStream, $readStreams);
        $writeObjectsByStream = [ ];
        $writeStreams = [ ];
        self::preprocessSet($writeObjects, $writeObjectsByStream, $writeStreams);
        $exceptObjectsByStream = [ ];
        $exceptStreams = [ ];
        self::preprocessSet($exceptObjects, $exceptObjectsByStream, $exceptStreams);
        $retval = stream_select($readStreams, $writeStreams, $exceptStreams, intval($seconds), intval(fmod($seconds, 1) * 1000000));
        if ($retval === false) {
            throw new Exception\RuntimeException('An I/O error occurred');
        }
        $objects = [ ];
        foreach ($streams as $stream) {
            $objects[] = $objectsByStream[intval($stream)];
        }

        return $retval;
    }

    /**
     * Watches for incoming data in one or many streams.
     *
     * @param array $objects On call, set of objects wrapping streams which must be watched for incoming data. On return, the set will only contain streams which actually have incoming data.
     * @param float $seconds Maximum number of seconds to wait for at least one stream to have incoming data.
     *
     * @return int Number of streams which actually have incoming data
     *
     * @throws Exception\InvalidArgumentException If the set is empty or contains anything else than selectables
     * @throws Exception\RuntimeException If an I/O operation fails
     */
    public static function selectRead(array &$objects, $seconds = 0)
    {
        $dummyW = [ ];
        $dummyE = [ ];

        return self::select($objects, $dummyW, $dummyE, $seconds);
    }

    /**
     * Watches for ability to write to one or many streams.
     *
     * @param array $objects On call, set of objects wrapping streams which must be watched for ability to write to them. On return, the set will only contain streams which can actually be written to.
     * @param float $seconds Maximum number of seconds to wait for at least one stream to be able to be written to.
     *
     * @return int Number of streams which can actually be written to
     *
     * @throws Exception\InvalidArgumentException If the set is empty or contains anything else than selectables
     * @throws Exception\RuntimeException If an I/O operation fails
     */
    public static function selectWrite(array &$objects, $seconds = 0)
    {
        $dummyR = [ ];
        $dummyE = [ ];

        return self::select($dummyR, $objects, $dummyE, $seconds);
    }

    /**
     * Watches for incoming high-priority exceptional ("out-of-band") data in one or many streams.
     *
     * @param array $objects On call, set of objects wrapping streams which must be watched for incoming high-priority data. On return, the set will only contain streams which actually have incoming high-priority data.
     * @param float $seconds Maximum number of seconds to wait for at least one stream to have incoming high-priority data.
     *
     * @return int Number of streams which actually have incoming high-priority data
     *
     * @throws Exception\InvalidArgumentException If the set is empty or contains anything else than selectables
     * @throws Exception\RuntimeException If an I/O operation fails
     */
    public static function selectExcept(array &$objects, $seconds = 0)
    {
        $dummyR = [ ];
        $dummyW = [ ];

        return self::select($dummyR, $dummyW, $objects, $seconds);
    }

    /**
     * Registers a listener to be notified when a stream has incoming data (requires {@link https://packagist.org/packages/react/event-loop reactphp/event-loop}).
     *
     * @param LoopInterface $loop Event loop in which to register the stream
     * @param OuterSource|SelectableInterface $object An object wrapping the stream to register
     * @param callable $listener A function to invoke when the stream has incoming data
     * @param boolean $once true to invoke the listener only for the next event, false to invoke it for each subsequent event until it is manually unregistered
     */
    public static function registerRead(LoopInterface $loop, $object, callable $listener, $once = false)
    {
        $stream = self::streamOf($object);
        if ($stream === null) {
            throw new Exception\InvalidArgumentException('The object must be, or wrap, a selectable');
        }
        $loop->addReadStream($stream, $once ? function () use ($loop, $object, $listener, $stream) {
            $loop->removeReadStream($stream);
            call_user_func($listener, $object, $loop);
        } : function () use ($loop, $object, $listener) {
            call_user_func($listener, $object, $loop);
        });
    }

    /**
     * Registers a listener to be notified when a stream is able to be written to (requires {@link https://packagist.org/packages/react/event-loop reactphp/event-loop}).
     *
     * @param LoopInterface $loop Event loop in which to register the stream
     * @param OuterSource|SelectableInterface $object An object wrapping the stream to register
     * @param callable $listener A function to invoke when the stream is able to be written to
     * @param boolean $once true to invoke the listener only for the next event, false to invoke it for each subsequent event until it is manually unregistered
     */
    public static function registerWrite(LoopInterface $loop, $object, callable $listener, $once = false)
    {
        $stream = self::streamOf($object);
        if ($stream === null) {
            throw new Exception\InvalidArgumentException('The object must be, or wrap, a selectable');
        }
        $loop->addWriteStream($stream, $once ? function () use ($loop, $object, $listener, $stream) {
            $loop->removeWriteStream($stream);
            call_user_func($listener, $object, $loop);
        } : function () use ($loop, $object, $listener) {
            call_user_func($listener, $object, $loop);
        });
    }

    /**
     * Unregisters the listener which was notified when a stream had incoming data (requires {@link https://packagist.org/packages/react/event-loop reactphp/event-loop}).
     *
     * @param LoopInterface $loop Event loop from which to unregister the stream
     * @param OuterSource|SelectableInterface $object An object wrapping the stream to unregister
     */
    public static function unregisterRead(LoopInterface $loop, $object)
    {
        $stream = self::streamOf($object);
        if ($stream === null) {
            throw new Exception\InvalidArgumentException('The object must be, or wrap, a selectable');
        }
        $loop->removeReadStream($stream);
    }

    /**
     * Unregisters the listener which was notified when a stream was able to be written to (requires {@link https://packagist.org/packages/react/event-loop reactphp/event-loop}).
     *
     * @param LoopInterface $loop Event loop from which to unregister the stream
     * @param OuterSource|SelectableInterface $object An object wrapping the stream to unregister
     */
    public static function unregisterWrite(LoopInterface $loop, $object)
    {
        $stream = self::streamOf($object);
        if ($stream === null) {
            throw new Exception\InvalidArgumentException('The object must be, or wrap, a selectable');
        }
        $loop->removeWriteStream($stream);
    }

    /**
     * Unregisters the listener which was notified when a stream had incoming data or was able to be written to (requires {@link https://packagist.org/packages/react/event-loop reactphp/event-loop}).
     *
     * @param LoopInterface $loop Event loop from which to unregister the stream
     * @param OuterSource|SelectableInterface $object An object wrapping the stream to unregister
     */
    public static function unregister(LoopInterface $loop, $object)
    {
        $stream = self::streamOf($object);
        if ($stream === null) {
            throw new Exception\InvalidArgumentException('The object must be, or wrap, a selectable');
        }
        $loop->removeStream($stream);
    }
}
