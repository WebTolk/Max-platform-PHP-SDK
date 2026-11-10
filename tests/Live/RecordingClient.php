<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Live;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class RecordingClient implements ClientInterface
{
    /** @var list<array<string, mixed>> */
    private array $records = [];

    public function __construct(private readonly ClientInterface $inner)
    {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $start = microtime(true);
        $record = [
            'method' => $request->getMethod(),
            'uri' => (string)$request->getUri()->withQuery(''),
            'request_body_sha256' => hash('sha256', (string)$request->getBody()),
        ];
        if ($request->getBody()->isSeekable()) {
            $request->getBody()->rewind();
        }
        try {
            $response = $this->inner->sendRequest($request);
            $body = (string)$response->getBody();
            if ($response->getBody()->isSeekable()) {
                $response->getBody()->rewind();
            }
            $record['status'] = $response->getStatusCode();
            $record['body_raw'] = $body;
            $record['body'] = json_decode($body, true);
            $record['response_body_sha256'] = hash('sha256', $body);

            return $response;
        } catch (\Throwable $error) {
            $record['transport_error_class'] = $error::class;
            throw $error;
        } finally {
            $record['duration_ms'] = (int)round((microtime(true) - $start) * 1000);
            $this->records[] = $record;
        }
    }

    /** @return list<array<string, mixed>> */
    public function flush(): array
    {
        $records = $this->records;
        $this->records = [];

        return $records;
    }
}
