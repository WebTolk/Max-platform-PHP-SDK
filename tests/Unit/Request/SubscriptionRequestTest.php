<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Request;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Interface\ApiTransportInterface;
use Webtolk\Max\Payload\CreateSubscriptionPayload;
use Webtolk\Max\Request\SubscriptionRequest;
use Webtolk\Max\Tests\Unit\Support\ResponseFactoryTrait;

final class SubscriptionRequestTest extends TestCase
{
    use ResponseFactoryTrait;

    public function testListBuildsSubscriptionListResponse(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'subscriptions' => [
                ['url' => 'https://example.com/hook'],
            ],
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('GET', '/subscriptions')
            ->willReturn($response);

        $request = new SubscriptionRequest($httpClient);
        $result = $request->list();

        $this->assertCount(1, $result->getSubscriptions());
    }

    public function testCreateSendsPayloadAndHydratesResult(): void
    {
        $payload = CreateSubscriptionPayload::create(
            'https://example.com/hook',
            ['message', 'callback'],
            'secret_1',
        );

        $response = $this->createJsonResponse($this, $this->encodeJson([
            'success' => true,
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with(
                'POST',
                '/subscriptions',
                [],
                [],
                [
                    'url' => 'https://example.com/hook',
                    'update_types' => ['message_created', 'message_callback'],
                    'secret' => 'secret_1',
                ],
            )
            ->willReturn($response);

        $request = new SubscriptionRequest($httpClient);
        $result = $request->create($payload);

        $this->assertTrue($result->isSuccess());
    }

    public function testDeleteSendsUrlQuery(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'success' => true,
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('DELETE', '/subscriptions', ['url' => 'https://example.com/hook'])
            ->willReturn($response);

        $request = new SubscriptionRequest($httpClient);
        $result = $request->delete('https://example.com/hook');

        $this->assertTrue($result->isSuccess());
    }
}
