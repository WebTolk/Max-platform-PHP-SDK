<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Request;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Webtolk\Max\Interface\ApiTransportInterface;
use Webtolk\Max\Payload\AddChatAdminsPayload;
use Webtolk\Max\Payload\AddChatMembersPayload;
use Webtolk\Max\Payload\ChatAdminAssignment;
use Webtolk\Max\Payload\PinChatMessagePayload;
use Webtolk\Max\Payload\SenderAction;
use Webtolk\Max\Payload\UpdateChatPayload;
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

    public function testUpdateBuildsPatchPayload(): void
    {
        $response = $this->createJsonResponse($this, json_encode([
            'chat_id' => 42,
            'title' => 'Новый чат',
        ]));

        $payload = UpdateChatPayload::create()
            ->withTitle('Новый чат')
            ->withNotify(true);

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with(
                'PATCH',
                '/chats/42',
                [],
                [],
                [
                    'title' => 'Новый чат',
                    'notify' => true,
                ],
            )
            ->willReturn($response);

        $request = new ChatRequest($httpClient);
        $result = $request->update(42, $payload);

        $this->assertSame('Новый чат', $result->getTitle());
    }

    public function testDeleteReturnsOperationResult(): void
    {
        $response = $this->createJsonResponse($this, json_encode([
            'success' => true,
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('DELETE', '/chats/42')
            ->willReturn($response);

        $request = new ChatRequest($httpClient);
        $result = $request->delete(42);

        $this->assertTrue($result->isSuccess());
    }

    public function testGetPinnedMessageHydratesMessageOrNull(): void
    {
        $messageResponse = $this->createJsonResponse($this, json_encode([
            'message' => [
                'body' => ['text' => 'pinned'],
            ],
        ]));
        $nullResponse = $this->createJsonResponse($this, json_encode([
            'message' => null,
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $calls = 0;
        $httpClient->expects($this->exactly(2))
            ->method('requestJson')
            ->willReturnCallback(function (string $method, string $uri) use (&$calls, $messageResponse, $nullResponse): ResponseInterface {
                $this->assertSame('GET', $method);
                $this->assertSame('/chats/5/pin', $uri);

                return $calls++ === 0 ? $messageResponse : $nullResponse;
            });

        $request = new ChatRequest($httpClient);

        $message = $request->getPinnedMessage(5);
        $missing = $request->getPinnedMessage(5);

        $this->assertSame('pinned', $message?->getBody()?->getText());
        $this->assertNull($missing);
    }

    public function testPinBuildsPutPayload(): void
    {
        $response = $this->createJsonResponse($this, json_encode([
            'success' => true,
        ]));

        $payload = PinChatMessagePayload::create('mid-1')->withNotify(false);

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with(
                'PUT',
                '/chats/8/pin',
                [],
                [],
                [
                    'message_id' => 'mid-1',
                    'notify' => false,
                ],
            )
            ->willReturn($response);

        $request = new ChatRequest($httpClient);
        $result = $request->pin(8, $payload);

        $this->assertTrue($result->isSuccess());
    }

    public function testUnpinReturnsOperationResult(): void
    {
        $response = $this->createJsonResponse($this, json_encode([
            'success' => true,
            'message' => 'unpinned',
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('DELETE', '/chats/8/pin')
            ->willReturn($response);

        $request = new ChatRequest($httpClient);
        $result = $request->unpin(8);

        $this->assertTrue($result->isSuccess());
        $this->assertSame('unpinned', $result->getMessage());
    }

    public function testAddMembersBuildsPostPayload(): void
    {
        $response = $this->createJsonResponse($this, json_encode([
            'success' => true,
        ]));

        $payload = AddChatMembersPayload::create(10, 20);

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with(
                'POST',
                '/chats/9/members',
                [],
                [],
                ['user_ids' => [10, 20]],
            )
            ->willReturn($response);

        $request = new ChatRequest($httpClient);
        $result = $request->addMembers(9, $payload);

        $this->assertTrue($result->isSuccess());
    }

    public function testRemoveMemberUsesDeleteQuery(): void
    {
        $response = $this->createJsonResponse($this, json_encode([
            'success' => true,
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('DELETE', '/chats/9/members', ['user_id' => 10, 'block' => true])
            ->willReturn($response);

        $request = new ChatRequest($httpClient);
        $result = $request->removeMember(9, 10, true);

        $this->assertTrue($result->isSuccess());
    }

    public function testLeaveUsesBotMemberEndpoint(): void
    {
        $response = $this->createJsonResponse($this, json_encode([
            'success' => true,
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('DELETE', '/chats/9/members/me')
            ->willReturn($response);

        $request = new ChatRequest($httpClient);
        $result = $request->leave(9);

        $this->assertTrue($result->isSuccess());
    }

    public function testAddAdminsBuildsPostPayload(): void
    {
        $response = $this->createJsonResponse($this, json_encode([
            'success' => true,
        ]));

        $payload = AddChatAdminsPayload::create(
            ChatAdminAssignment::forUser(10)
                ->withPermissions('write', 'pin_message')
                ->withAlias('Admin'),
        );

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with(
                'POST',
                '/chats/9/members/admins',
                [],
                [],
                [
                    'admins' => [[
                        'user_id' => 10,
                        'permissions' => ['write', 'pin_message'],
                        'alias' => 'Admin',
                    ]],
                ],
            )
            ->willReturn($response);

        $request = new ChatRequest($httpClient);
        $result = $request->addAdmins(9, $payload);

        $this->assertTrue($result->isSuccess());
    }

    public function testRemoveAdminUsesDeleteEndpoint(): void
    {
        $response = $this->createJsonResponse($this, json_encode([
            'success' => true,
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('DELETE', '/chats/9/members/admins/10')
            ->willReturn($response);

        $request = new ChatRequest($httpClient);
        $result = $request->removeAdmin(9, 10);

        $this->assertTrue($result->isSuccess());
    }

    public function testSendActionBuildsRequestBody(): void
    {
        $response = $this->createJsonResponse($this, json_encode([
            'success' => true,
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with(
                'POST',
                '/chats/9/actions',
                [],
                [],
                ['action' => SenderAction::TYPING_ON->value],
            )
            ->willReturn($response);

        $request = new ChatRequest($httpClient);
        $result = $request->sendAction(9, SenderAction::TYPING_ON);

        $this->assertTrue($result->isSuccess());
    }
}
