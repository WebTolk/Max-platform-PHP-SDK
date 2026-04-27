<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Request;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Webtolk\Max\Interface\ApiTransportInterface;
use Webtolk\Max\Query\ChatMembersQuery;
use Webtolk\Max\Request\ChatRequest;
use Webtolk\Max\Tests\Unit\Support\ResponseFactoryTrait;

final class ChatRequestTest extends TestCase
{
    use ResponseFactoryTrait;

    public function testListBuildsChatListResponse(): void
    {
        $response = $this->createJsonResponse($this, json_encode([
            'chats' => [['id' => 1]],
            'marker' => 10,
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('GET', '/chats', ['marker' => 10, 'count' => 25])
            ->willReturn($response);

        $request = new ChatRequest($httpClient);
        $result = $request->list(10, 25);

        $this->assertSame(10, $result->getMarker());
    }

    public function testGetByIdUsesEndpoint(): void
    {
        $response = $this->createJsonResponse($this, json_encode([
            'chat_id' => 42,
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('GET', '/chats/42')
            ->willReturn($response);

        $request = new ChatRequest($httpClient);
        $result = $request->getById(42);

        $this->assertSame(42, $result->getId());
    }

    public function testMembersDefaultsToPagingQuery(): void
    {
        $response = $this->createJsonResponse($this, json_encode([
            'members' => [['user_id' => 5]],
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('GET', '/chats/1/members', [])
            ->willReturn($response);

        $request = new ChatRequest($httpClient);
        $result = $request->members(1);

        $this->assertCount(1, $result->getMembers());
        $this->assertNull($result->getMarker());
    }

    public function testMembersUsesCustomQuery(): void
    {
        $query = ChatMembersQuery::forUsers(100, 200);
        $response = $this->createJsonResponse($this, json_encode([
            'members' => [['user_id' => 100], ['user_id' => 200]],
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('GET', '/chats/2/members', ['user_ids' => [100, 200]])
            ->willReturn($response);

        $request = new ChatRequest($httpClient);
        $result = $request->members(2, $query);

        $this->assertCount(2, $result->getMembers());
    }

    public function testMemberMeAndAdminsUseRightEndpoints(): void
    {
        $response = $this->createJsonResponse($this, json_encode([
            'user_id' => 9,
        ]));
        $adminResponse = $this->createJsonResponse($this, json_encode([
            'members' => [['user_id' => 9], ['user_id' => 10]],
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $calls = 0;
        $httpClient->expects($this->exactly(2))
            ->method('requestJson')
            ->willReturnCallback(function (string $method, string $uri) use (&$calls, $response, $adminResponse): ResponseInterface {
                if ($calls === 0) {
                    $this->assertSame('GET', $method);
                    $this->assertSame('/chats/3/members/me', $uri);
                    $calls++;
                    return $response;
                }

                $this->assertSame(1, $calls);
                $this->assertSame('GET', $method);
                $this->assertSame('/chats/3/members/admins', $uri);
                $calls++;
                return $adminResponse;
            });

        $request = new ChatRequest($httpClient);
        $member = $request->memberMe(3);
        $admins = $request->admins(3);

        $this->assertSame(9, $member->toArray()['user_id'] ?? null);
        $this->assertCount(2, $admins->getMembers());
    }
}
