[![Build Status](https://scrutinizer-ci.com/g/EXSyst/IO/badges/build.png?b=master)](https://scrutinizer-ci.com/g/EXSyst/IO/build-status/master)
[![Build Status](https://travis-ci.org/EXSyst/IO.svg?branch=master)](https://travis-ci.org/EXSyst/IO)

# EXSyst I/O Component
Object-oriented I/O facility

## ```Source```s
The ```Source``` objects (objects implementing ```SourceInterface```) can be used to read raw chunks of data from several sources :
- ```StringSource```s can be used to read data from a string ;
- ```StreamSource```s can be used to read data from a PHP stream resource, **without protection from most flaws** in the implementation of the wrapped PHP stream ; they can also be used as ```Sink```s (read more in the next section) if the stream is writable or bidirectional ;
- ```BufferedSource```s can be used to wrap other sources, in order to fix several flaws which their implementation may have, and add missing features (for example, haven't you already needed, if only once, to seek backwards on a socket ?) ;
- ```OuterSource```s are abstract : you may extend them to provide additional services on existing ```Source```s (for example, ```BufferedSource```s and ```Reader```s are ```OuterSource```s).

You may create ```Source```s directly from the classes' constructors, or using static methods from the ```Source``` class, which may provide additional services (for example, ```fromStream``` and ```fromFile``` automatically wrap the ```StreamSource```s in ```BufferedSource```s by default).

The ```Source``` class also provides static utility methods.

## ```Sink```s
The ```Sink``` objects (objects implementing ```SinkInterface```) can be used to write raw chunks of data to several sinks :
- ```StringSink```s can be used to build strings (but may be slower than plain concatenation : beware of call overhead !) ;
- The ```SystemSink``` is a simple wrapper for ```echo``` ; it is a singleton ;
- ```RecordFunctionSink```s can be used to aggregate data into records (of fixed size, or delimited by a separator, with an optional size limit) and pass them to a custom function ;
- ```StreamSource```s (see the previous section) which wrap a writable or bidirectional stream can be used to write data to a PHP stream resource ;
- ```TeeSink```s can be used to duplicate data into multiple ```Sink```s.

Like the ```Source``` class, the ```Sink``` class provides static methods which can be used to easily create ```Sink```s, and static utility methods.

## ```State```s
The ```State``` objects (objects implementing ```StateInterface```) can be obtained by calling ```captureState``` on a ```Source``` which supports it.
They can be used to rewind the ```Source``` to a previous position using the ```restore``` method.

You can wrap your ```Source``` in a ```BufferedSource``` if you need to rewind it and if it doesn't support it.

## ```Reader```s
The ```Reader``` objects (which do not implement any specific interface, as they provide much different services) can be used to read structured data from ```Source```s :
- ```CDataReader```s can be used to ease the writing of lexers : they support ```eat```ing fixed strings, strings including or excluding only given character classes, and white space ;
- ```StringCDataReader```s are ```CDataReader```s optimized for ```StringSource```s, which additionally suppport ```eat```ing strings matching a regular expression (using ```preg_match```) ;
- ```SerializedReader```s can be used to separate concatenated ```serialize```d values (as in ```serialize($foo).serialize($bar)```), regardless of whether they come from a local ```Source``` (such as a string or a file stream) or a remote one (such as a pipe or network stream) ; they are explicitly designed to work efficiently with remote ```Source```s ;
- ```JsonReader```s can be used to separate concatenated JSON (as specified by [RFC 7159](https://tools.ietf.org/html/rfc7159)) values, in the same conditions as ```SerializedReader```.

It is recommended to create ```CDataReader```s using the ```fromSource``` static method : it will automatically prefer an optimized implementation (such as ```StringCDataReader```) when applicable.

## ```Channel```s
The ```Channel``` objects (objects implementing ```ChannelInterface```) can be used to communicate with remote tasks using messages, which will be serialized if necessary :
- ```SerializedChannel```s serialize the messages using the native PHP format (with ```serialize```) ;
- ```JsonChannel```s serialize the messages using JSON (as specified by [RFC 7159](https://tools.ietf.org/html/rfc7159)).

The ```ChannelFactory``` objects (objects implementing ```ChannelFactoryInterface```) can be used by an application or a library to specify an encoder/decoder couple, along with their parameters, to another library.

## ```Selectable```s
The ```Selectable``` objects (objects implementing ```SelectableInterface```) are objects which wrap PHP streams. They can be passed to the static methods of the ```Selectable``` class, which are object-oriented wrappers to ```stream_select```. These methods can look for ```Selectable```s wrapped in arbitrarily many ```OuterSource```s, and will let the originally passed objects (not the inner ```Selectable```s) in the sets on return.

Many objects are ```Selectable``` : ```StreamSource```s, all ```Channel```s, and objects of the ```Selectable``` class itself : in addition to its static utility methods, it provides a bare-bones implementation of the interface (which you can use to, for example, add a server socket to your set).
