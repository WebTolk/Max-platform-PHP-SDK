<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Support;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

trait ResponseFactoryTrait
{
    /**
     * @throws Exception
     */
    protected function createResponse(TestCase $case, string $body): ResponseInterface
    {
        $stream = $case->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn($body);

        $response = $case->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        return $response;
    }

    /**
     * @throws Exception
     */
    protected function createJsonResponse(TestCase $case, string $json): ResponseInterface
    {
        return $this->createResponse($case, $json);
    }
}
