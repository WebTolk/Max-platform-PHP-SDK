<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Http;

use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\StreamFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Stringable;
use Webtolk\Max\Config\MaxConfig;
use Webtolk\Max\Exception\TransportException;
use Webtolk\Max\Http\PsrHttpClient;

final class PsrHttpClientTest extends TestCase
{
    public function testRequestJsonLogsSanitizedUriContextWithoutQueryValues(): void
    {
        $logger = new TestLogger();
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->with($this->isInstanceOf(RequestInterface::class))
            ->willReturn($this->createJsonResponse('{"ok":true}'));

        $transport = new PsrHttpClient(
            $client,
            new RequestFactory(),
            new StreamFactory(),
            new MaxConfig('secret-token'),
            $logger,
        );

        $transport->requestJson('GET', '/messages', [
            'chat_id' => 'chat-1',
            'callback_id' => 'cb-123',
            'sig' => 'signed-value',
        ]);

        $requestRecord = $logger->findRecord('debug', 'MAX API request');

        $this->assertNotNull($requestRecord);
        $this->assertSame('GET', $requestRecord['context']['method']);
        $this->assertSame([
            'scheme' => 'https',
            'host' => 'platform-api2.max.ru',
            'path' => '/messages',
            'query_keys' => ['chat_id', 'callback_id', 'sig'],
        ], $requestRecord['context']['uri']);
        $this->assertArrayNotHasKey('query', $requestRecord['context']['uri']);
        $this->assertSame(['[REDACTED]'], $requestRecord['context']['headers']['Authorization']);
    }

    public function testRequestBinaryLogsSanitizedUploadUriContext(): void
    {
        $logger = new TestLogger();
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->with($this->isInstanceOf(RequestInterface::class))
            ->willReturn($this->createJsonResponse('{"token":"uploaded"}'));

        $transport = new PsrHttpClient(
            $client,
            new RequestFactory(),
            new StreamFactory(),
            new MaxConfig('secret-token'),
            $logger,
        );

        $transport->requestBinary(
            'https://vu.okcdn.ru/upload?sig=signed-value&expires=123456&id=file-1',
            'payload',
            'application/octet-stream',
        );

        $requestRecord = $logger->findRecord('debug', 'MAX API request');

        $this->assertNotNull($requestRecord);
        $this->assertSame([
            'scheme' => 'https',
            'host' => 'vu.okcdn.ru',
            'path' => '/upload',
            'query_keys' => ['sig', 'expires', 'id'],
        ], $requestRecord['context']['uri']);
        $this->assertArrayNotHasKey('query', $requestRecord['context']['uri']);
    }

    public function testTransportErrorLogsSanitizedUriContext(): void
    {
        $logger = new TestLogger();
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('sendRequest')
            ->willThrowException(new TestClientException('boom'));

        $transport = new PsrHttpClient(
            $client,
            new RequestFactory(),
            new StreamFactory(),
            new MaxConfig('secret-token'),
            $logger,
        );

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('boom');

        try {
            $transport->requestJson('POST', '/subscriptions', [
                'url' => 'https://example.com/webhook?token=internal-secret',
                'token' => 'internal-secret',
            ], [], ['event_types' => ['message_created']]);
        } finally {
            $warningRecord = $logger->findRecord('warning', 'MAX API transport error');

            $this->assertNotNull($warningRecord);
            $this->assertSame([
                'scheme' => 'https',
                'host' => 'platform-api2.max.ru',
                'path' => '/subscriptions',
                'query_keys' => ['url', 'token'],
            ], $warningRecord['context']['uri']);
            $this->assertArrayNotHasKey('query', $warningRecord['context']['uri']);
        }
    }

    private function createJsonResponse(string $body): Response
    {
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            self::fail('Unable to open temporary stream.');
        }

        fwrite($stream, $body);
        rewind($stream);

        return new Response(new Stream($stream), 200, ['Content-Type' => 'application/json']);
    }
}

final class TestLogger implements LoggerInterface
{
    /**
     * @var list<array{level: string, message: string, context: array<string, mixed>}>
     */
    public array $records = [];

    public function emergency(Stringable|string $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    public function alert(Stringable|string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    public function critical(Stringable|string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    public function error(Stringable|string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning(Stringable|string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function notice(Stringable|string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    public function info(Stringable|string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function debug(Stringable|string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        $this->records[] = [
            'level' => (string)$level,
            'message' => (string)$message,
            'context' => $context,
        ];
    }

    /**
     * @return array{level: string, message: string, context: array<string, mixed>}|null
     */
    public function findRecord(string $level, string $message): ?array
    {
        foreach ($this->records as $record) {
            if ($record['level'] === $level && $record['message'] === $message) {
                return $record;
            }
        }

        return null;
    }
}

final class TestClientException extends \RuntimeException implements ClientExceptionInterface
{
}
