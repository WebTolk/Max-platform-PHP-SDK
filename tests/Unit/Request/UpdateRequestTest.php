<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Request;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Interface\ApiTransportInterface;
use Webtolk\Max\Query\GetUpdatesQuery;
use Webtolk\Max\Request\UpdateRequest;
use Webtolk\Max\Tests\Unit\Support\ResponseFactoryTrait;

final class UpdateRequestTest extends TestCase
{
    use ResponseFactoryTrait;

    public function testListWithoutQueryUsesDefaultQuery(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'updates' => [
                ['update_type' => 'message'],
            ],
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('GET', '/updates', [])
            ->willReturn($response);

        $request = new UpdateRequest($httpClient);
        $result = $request->list();

        $this->assertCount(1, $result->getUpdates());
    }

    public function testListUsesProvidedQuery(): void
    {
        $query = GetUpdatesQuery::fromMarker(100)->withLimit(10);
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'updates' => [],
            'marker' => 101,
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('GET', '/updates', $query->toQueryParams())
            ->willReturn($response);

        $request = new UpdateRequest($httpClient);
        $result = $request->list($query);

        $this->assertSame(101, $result->getMarker());
    }
}
