<?php

namespace EXSyst\Component\IO\Tests\Channel;

use EXSyst\Component\IO\Channel\ChannelInterface;
use EXSyst\Component\IO\Channel\JsonChannel;
use EXSyst\Component\IO\Sink\SinkInterface;
use EXSyst\Component\IO\Source\SourceInterface;
use EXSyst\Component\IO\Source\StringSource;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class JsonChannelTest extends \PHPUnit_Framework_TestCase
{
    public function testInterface()
    {
        list($channel) = $this->createChannel();
        $this->assertInstanceOf(ChannelInterface::class, $channel);
    }

    public function testWrite()
    {
        list($channel, $sink) = $this->createChannel();

        $sink->expects($this->at(0))
            ->method('write')
            ->with('"foo"'."\n");
        $sink->expects($this->at(1))
            ->method('write')
            ->with('null'."\n");

        $channel->sendMessage('foo');
        $channel->sendMessage(null);
    }

    /**
     * @expectedException EXSyst\Component\IO\Exception\EncodingException
     * @expectedExceptionMessage Maximum stack depth exceeded
     *
     * Bug detected in hhvm: if you pass an empty array hhvm doesn't detect that the max stack depth is exceeded.
     * $channel->sendMessage([[[]]]);
     */
    public function testInvalidWrite()
    {
        list($channel, $sink) = $this->createChannel('', 0, false, 2);

        $channel->sendMessage([[['foo']]]);
    }

    /**
     * @param string|SourceInterface $content
     *
     * @return array
     */
    private function createChannel($source = '', $encoderOptions = 0, $assoc = false, $depth = 512, $decoderOptions = 0)
    {
        $sink = $this->getMock(SinkInterface::class);
        if (is_string($source)) {
            $source = new StringSource($source);
        }
        $channel = new JsonChannel($source, $sink, $encoderOptions, $assoc, $depth, $decoderOptions);

        return [$channel, $sink];
    }
}
