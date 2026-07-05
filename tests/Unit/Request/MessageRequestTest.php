<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Request;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Interface\ApiTransportInterface;
use Webtolk\Max\Payload\CallbackAnswerPayload;
use Webtolk\Max\Payload\EditMessageBody;
use Webtolk\Max\Payload\NewMessageBody;
use Webtolk\Max\Query\MessageQuery;
use Webtolk\Max\Request\MessageRequest;
use Webtolk\Max\Tests\Unit\Support\ResponseFactoryTrait;

final class MessageRequestTest extends TestCase
{
    use ResponseFactoryTrait;

    public function testSendToChatBuildsRequestPayload(): void
    {
        $body = NewMessageBody::text('Hello');

        $response = $this->createJsonResponse($this, $this->encodeJson([
            'message' => [
                'body' => ['text' => 'Hello'],
            ],
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with(
                'POST',
                '/messages',
                ['chat_id' => 123, 'disable_link_preview' => null],
                [],
                ['text' => 'Hello'],
            )
            ->willReturn($response);

        $request = new MessageRequest($httpClient);
        $result = $request->sendToChat(123, $body);

        $this->assertSame('Hello', $result->getBody()?->getText());
    }

    public function testSendToChatPassesDisableLinkPreviewFlag(): void
    {
        $body = NewMessageBody::text('Hello');

        $response = $this->createJsonResponse($this, $this->encodeJson([
            'message' => [
                'body' => ['text' => 'Hello'],
            ],
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with(
                'POST',
                '/messages',
                ['chat_id' => 123, 'disable_link_preview' => true],
                [],
                ['text' => 'Hello'],
            )
            ->willReturn($response);

        $request = new MessageRequest($httpClient);
        $result = $request->sendToChat(123, $body, true);

        $this->assertSame('Hello', $result->getBody()?->getText());
    }

    public function testGetByIdUsesMessageEndpoint(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'sender' => ['id' => 1],
            'body' => ['text' => 'x'],
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('GET', '/messages/m1')
            ->willReturn($response);

        $request = new MessageRequest($httpClient);
        $result = $request->getById('m1');

        $this->assertSame('x', $result->getBody()?->getText());
    }

    public function testGetByIdEncodesMessageIdPathSegment(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'body' => ['text' => 'encoded'],
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('GET', '/messages/mid%2F1')
            ->willReturn($response);

        $request = new MessageRequest($httpClient);
        $result = $request->getById('mid/1');

        $this->assertSame('encoded', $result->getBody()?->getText());
    }

    public function testGetByQueryIdPreservesLegacyMessageIdsLookup(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'messages' => [[
                'sender' => ['id' => 1],
                'body' => ['text' => 'legacy'],
            ]],
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('GET', '/messages', ['message_ids' => ['m1']])
            ->willReturn($response);

        $request = new MessageRequest($httpClient);
        $result = $request->getByQueryId('m1');

        $this->assertSame('legacy', $result->getBody()?->getText());
    }

    public function testSendToUserBuildsRequestPayload(): void
    {
        $body = NewMessageBody::text('Private hello');

        $response = $this->createJsonResponse($this, $this->encodeJson([
            'message' => [
                'body' => ['text' => 'Private hello'],
            ],
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with(
                'POST',
                '/messages',
                ['user_id' => 777, 'disable_link_preview' => null],
                [],
                ['text' => 'Private hello'],
            )
            ->willReturn($response);

        $request = new MessageRequest($httpClient);
        $result = $request->sendToUser(777, $body);

        $this->assertSame('Private hello', $result->getBody()?->getText());
    }

    public function testSendToUserPassesDisableLinkPreviewFlag(): void
    {
        $body = NewMessageBody::text('Private hello');

        $response = $this->createJsonResponse($this, $this->encodeJson([
            'message' => [
                'body' => ['text' => 'Private hello'],
            ],
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with(
                'POST',
                '/messages',
                ['user_id' => 777, 'disable_link_preview' => false],
                [],
                ['text' => 'Private hello'],
            )
            ->willReturn($response);

        $request = new MessageRequest($httpClient);
        $result = $request->sendToUser(777, $body, false);

        $this->assertSame('Private hello', $result->getBody()?->getText());
    }

    public function testListUsesQueryObject(): void
    {
        $query = MessageQuery::forChat(3)->withCount(2);
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'messages' => [['body' => ['text' => 'a']]],
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with(
                'GET',
                '/messages',
                $query->toQueryParams(),
            )
            ->willReturn($response);

        $request = new MessageRequest($httpClient);
        $result = $request->list($query);

        $this->assertSame('a', $result->getMessages()[0]->getBody()?->getText());
    }

    public function testEditReturnsOperationResult(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'success' => true,
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('PUT', '/messages', ['message_id' => 'm1'], [], ['text' => 'new'])
            ->willReturn($response);

        $request = new MessageRequest($httpClient);
        $result = $request->edit('m1', EditMessageBody::text('new'));

        $this->assertTrue($result->isSuccess());
    }

    public function testDeleteReturnsOperationResult(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'success' => true,
            'message' => 'deleted',
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('DELETE', '/messages', ['message_id' => 'm2'])
            ->willReturn($response);

        $request = new MessageRequest($httpClient);
        $result = $request->delete('m2');

        $this->assertTrue($result->isSuccess());
        $this->assertSame('deleted', $result->getMessage());
    }

    public function testAnswerCallbackBuildsPostPayload(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'success' => true,
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with(
                'POST',
                '/answers',
                ['callback_id' => 'cb1'],
                [],
                ['message' => ['text' => 'accepted']],
            )
            ->willReturn($response);

        $request = new MessageRequest($httpClient);
        $result = $request->answerCallback('cb1', CallbackAnswerPayload::fromMessage(NewMessageBody::text('accepted')));

        $this->assertTrue($result->isSuccess());
    }
}
