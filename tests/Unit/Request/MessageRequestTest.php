<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Request;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Interface\ApiTransportInterface;
use Webtolk\Max\Payload\CallbackAnswerPayload;
use Webtolk\Max\Payload\EditMessageBody;
use Webtolk\Max\Payload\NewCommentBody;
use Webtolk\Max\Payload\NewMessageBody;
use Webtolk\Max\Query\CommentQuery;
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

    public function testSendCommentBuildsEncodedRequestAndHydratesWrappedMessage(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'message' => ['body' => ['text' => 'Комментарий']],
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with(
                'POST',
                '/messages/mid%2F1/comments',
                ['disable_link_preview' => true],
                [],
                ['text' => 'Комментарий'],
            )
            ->willReturn($response);

        $request = new MessageRequest($httpClient);
        $result = $request->sendComment('mid/1', NewCommentBody::text('Комментарий'), true);

        $this->assertSame('Комментарий', $result->getBody()?->getText());
    }

    public function testListCommentsUsesQueryAndHydratesCollection(): void
    {
        $query = CommentQuery::all()->withCount(2);
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'messages' => [
                ['body' => ['text' => 'Первый']],
                ['body' => ['text' => 'Второй']],
            ],
            'marker' => 42,
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('GET', '/messages/post-1/comments', ['count' => 2])
            ->willReturn($response);

        $request = new MessageRequest($httpClient);
        $result = $request->listComments('post-1', $query);

        $this->assertSame('Первый', $result->getMessages()[0]->getBody()?->getText());
        $this->assertSame(42, $result->getMarker());
    }

    public function testGetCommentEncodesBothPathSegments(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'body' => ['text' => 'Ответ'],
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('GET', '/messages/post%2F1/comments/comment%2F2')
            ->willReturn($response);

        $request = new MessageRequest($httpClient);
        $result = $request->getComment('post/1', 'comment/2');

        $this->assertSame('Ответ', $result->getBody()?->getText());
    }

    public function testEditCommentUsesCommentIdQuery(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson(['success' => true]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with(
                'PUT',
                '/messages/post-1/comments',
                ['comment_id' => 'comment-2'],
                [],
                ['text' => 'Изменено'],
            )
            ->willReturn($response);

        $request = new MessageRequest($httpClient);

        $this->assertTrue(
            $request->editComment('post-1', 'comment-2', NewCommentBody::text('Изменено'))->isSuccess(),
        );
    }

    public function testDeleteCommentUsesCommentIdQuery(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson(['success' => true]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('DELETE', '/messages/post-1/comments', ['comment_id' => 'comment-2'])
            ->willReturn($response);

        $request = new MessageRequest($httpClient);

        $this->assertTrue($request->deleteComment('post-1', 'comment-2')->isSuccess());
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
                ['callback_id' => 'cb1', 'disable_link_preview' => null],
                [],
                ['message' => ['text' => 'accepted']],
            )
            ->willReturn($response);

        $request = new MessageRequest($httpClient);
        $result = $request->answerCallback('cb1', CallbackAnswerPayload::fromMessage(NewMessageBody::text('accepted')));

        $this->assertTrue($result->isSuccess());
    }

    public function testAnswerCallbackPassesDisableLinkPreviewFlag(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson(['success' => true]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with(
                'POST',
                '/answers',
                ['callback_id' => 'cb1', 'disable_link_preview' => false],
                [],
                ['message' => ['text' => 'accepted']],
            )
            ->willReturn($response);

        $request = new MessageRequest($httpClient);
        $result = $request->answerCallback(
            'cb1',
            CallbackAnswerPayload::fromMessage(NewMessageBody::text('accepted')),
            false,
        );

        $this->assertTrue($result->isSuccess());
    }
}
