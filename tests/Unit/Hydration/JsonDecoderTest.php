<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Hydration;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Webtolk\Max\Exception\HydrationException;
use Webtolk\Max\Hydration\JsonDecoder;

final class JsonDecoderTest extends TestCase
{
    public function testDecodeReturnsArray(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn('{"ok":1}');
        $response->method('getBody')->willReturn($stream);

        $this->assertSame(['ok' => 1], JsonDecoder::decode($response));
    }

    public function testDecodeRejectsNonArrayPayload(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn('true');
        $response->method('getBody')->willReturn($stream);

        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Response JSON is not an associative or sequential array');

        JsonDecoder::decode($response);
    }

    public function testDecodeRejectsInvalidJson(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn('{');
        $response->method('getBody')->willReturn($stream);

        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Response JSON is invalid');

        JsonDecoder::decode($response);
    }
}
