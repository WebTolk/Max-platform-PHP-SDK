<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Entity\BotCommand;
use Webtolk\Max\Entity\ChatList;
use Webtolk\Max\Entity\CommentLinkedMessage;
use Webtolk\Max\Entity\CommentMessage;
use Webtolk\Max\Entity\CommentMessageBody;
use Webtolk\Max\Entity\LinkedMessage;
use Webtolk\Max\Entity\MessageStat;
use Webtolk\Max\Entity\Update;
use Webtolk\Max\Entity\UploadResult;
use Webtolk\Max\Entity\UploadUrl;
use Webtolk\Max\Exception\AttachmentNotReadyException;
use Webtolk\Max\Exception\RateLimitException;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Payload\Attachment\AudioAttachment;
use Webtolk\Max\Payload\Attachment\Button\LinkButton;
use Webtolk\Max\Payload\Attachment\ImageAttachment;
use Webtolk\Max\Payload\Attachment\VideoAttachment;
use Webtolk\Max\Payload\EditMessageBody;
use Webtolk\Max\Payload\NewCommentBody;
use Webtolk\Max\Payload\NewMessageBody;
use Webtolk\Max\Payload\SenderAction;
use Webtolk\Max\Payload\TextFormat;
use Webtolk\Max\Payload\UploadType;
use Webtolk\Max\Query\CommentQuery;
use Webtolk\Max\Query\MessageQuery;

/** Regression cases for public helpers beyond the network module operations. */
final class PublicContractTest extends TestCase
{
    public function testLinkedEntitiesPreserveTheirDifferentIdTypes(): void
    {
        $message = ['mid' => 'synthetic-message', 'text' => 'Example', 'markup' => []];
        $user = ['user_id' => 7, 'first_name' => 'Example', 'is_bot' => false];
        $linked = new LinkedMessage(['type' => 'reply', 'chat_id' => -42, 'sender' => $user, 'message' => $message]);
        $comment = new CommentLinkedMessage(['type' => 'reply', 'chat_id' => -42, 'message_id' => 'synthetic-message', 'message' => $message]);

        $this->assertSame('reply', $linked->getType());
        $this->assertSame('-42', $linked->getChatId());
        $this->assertSame(7, $linked->getSender()?->getId());
        $this->assertSame('Example', $linked->getMessage()?->getText());
        $this->assertSame('reply', $comment->getType());
        $this->assertSame('-42', $comment->getChatId());
        $this->assertSame('synthetic-message', $comment->getMessageId());
        $this->assertSame('Example', $comment->getMessage()?->getText());
        $this->assertNull((new LinkedMessage())->getMessage());
        $this->assertNull((new CommentLinkedMessage())->getMessageId());
    }

    public function testCommentParticipantsAndMarkupRemainOptional(): void
    {
        $comment = new CommentMessage([
            'sender' => ['user_id' => 7],
            'recipient' => ['chat_id' => -42, 'chat_type' => 'channel'],
        ]);
        $this->assertSame(7, $comment->getSender()?->getId());
        $this->assertNotNull($comment->getRecipient());
        $this->assertNull((new CommentMessage())->getSender());
        $this->assertNull((new CommentMessage())->getRecipient());
        $this->assertSame([], (new CommentMessageBody())->getMarkup());
        $this->assertSame([['type' => 'strong', 'from' => 0, 'length' => 1]], (new CommentMessageBody([
            'markup' => [['type' => 'strong', 'from' => 0, 'length' => 1]],
        ]))->getMarkup());
    }

    public function testExistingEventAndCollectionAccessorsStillHydrate(): void
    {
        $update = new Update(['message' => ['body' => ['text' => 'Example']], 'user_locale' => 'ru']);
        $this->assertSame('Example', $update->getMessage()?->getBody()?->getText());
        $this->assertSame('ru', $update->getUserLocale());
        $this->assertNull((new Update())->getMessage());
        $this->assertNull((new Update())->getUserLocale());
        $this->assertSame(12, (new MessageStat(['views' => 12]))->getViews());
        $this->assertSame('Example', (new BotCommand(['name' => 'start', 'description' => 'Example']))->getDescription());
        $this->assertSame(-42, (new ChatList(['chats' => [['chat_id' => -42]]]))->getChats()[0]->getId());
        $this->assertSame([], (new ChatList())->getChats());
    }

    public function testMediaTokensBecomeTheCorrectWireAttachmentTypes(): void
    {
        foreach ([AudioAttachment::class => 'audio', ImageAttachment::class => 'image', VideoAttachment::class => 'video'] as $class => $type) {
            $this->assertSame(['type' => $type, 'payload' => ['token' => 'synthetic-token']], $class::fromToken('synthetic-token')->toRequestArray());
        }
        $result = new UploadResult([], UploadType::FILE, 'synthetic-token');
        $url = new UploadUrl(['token' => 'synthetic-token'], UploadType::IMAGE);
        $this->assertTrue($result->hasToken());
        $this->assertTrue($url->hasToken());
        $this->assertSame('file', $result->toAttachment()->toRequestArray()['type']);
        $this->assertSame('image', $url->toAttachment()->toRequestArray()['type']);
        $this->assertFalse((new UploadResult([], UploadType::FILE))->hasToken());
        $this->assertFalse((new UploadUrl([], UploadType::IMAGE))->hasToken());
    }

    public function testLinkButtonAndTextFormatsSerializeWithoutRenaming(): void
    {
        $this->assertSame(['type' => 'link', 'text' => 'Example', 'url' => 'https://example.com'], LinkButton::create('Example', 'https://example.com')->toRequestArray());
        $this->assertSame('html', NewMessageBody::html('<b>Example</b>')->toRequestArray()['format']);
        $this->assertSame('markdown', NewMessageBody::markdown('*Example*')->toRequestArray()['format']);
        $this->assertSame('html', NewCommentBody::html('<b>Example</b>')->toRequestArray()['format']);
        $this->assertSame('markdown', NewCommentBody::markdown('*Example*')->toRequestArray()['format']);
        $this->assertSame(['attachments' => []], (new EditMessageBody())->clearAttachments()->toRequestArray());
    }

    public function testBackedEnumsRoundTripAllPublicWireValues(): void
    {
        foreach ([UploadType::class, SenderAction::class, TextFormat::class] as $enum) {
            foreach ($enum::cases() as $case) {
                $this->assertSame($case, $enum::from($case->value));
                $this->assertSame($case, $enum::tryFrom($case->value));
            }
            $this->assertNull($enum::tryFrom('synthetic-unknown-value'));
        }
    }

    public function testTimestampAliasesKeepDocumentedQueryKeys(): void
    {
        $this->assertSame(['before' => 200, 'after' => 100], CommentQuery::all()->beforeTimestamp(200)->afterTimestamp(100)->toQueryParams());
        $this->assertSame(['chat_id' => -42, 'from' => 200, 'to' => 100], MessageQuery::forChat(-42)->between(200, 100)->toQueryParams());
    }

    public function testExceptionSpecializationsPreserveStatusCauseAndErrors(): void
    {
        $cause = new \RuntimeException('synthetic cause');
        $attachment = new AttachmentNotReadyException('Not ready', $cause);
        $rate = new RateLimitException('Limited', previous: $cause);
        $this->assertSame(409, $attachment->statusCode);
        $this->assertSame('attachment.not.ready', $attachment->apiCode);
        $this->assertSame($cause, $attachment->getPrevious());
        $this->assertSame(429, $rate->statusCode);
        $this->assertSame($cause, $rate->getPrevious());
        $this->assertSame(['field' => 'invalid'], (new ValidationException('Invalid', ['field' => 'invalid']))->getErrors());
    }
}
