<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Live;

use JsonSerializable;
use ReflectionMethod;
use Throwable;
use Webtolk\Max\Entity\AbstractEntity;
use Webtolk\Max\Entity\Message;
use Webtolk\Max\Entity\OperationResult;
use Webtolk\Max\Entity\UploadUrl;
use Webtolk\Max\Exception\ApiException;
use Webtolk\Max\Exception\AttachmentNotReadyException;
use Webtolk\Max\Max;
use Webtolk\Max\Payload\AddChatAdminsPayload;
use Webtolk\Max\Payload\AddChatMembersPayload;
use Webtolk\Max\Payload\Attachment\Button\CallbackButton;
use Webtolk\Max\Payload\Attachment\FileAttachment;
use Webtolk\Max\Payload\Attachment\InlineKeyboardAttachment;
use Webtolk\Max\Payload\BotCommandPayload;
use Webtolk\Max\Payload\BotCommandsPayload;
use Webtolk\Max\Payload\CallbackAnswerPayload;
use Webtolk\Max\Payload\ChatAdminAssignment;
use Webtolk\Max\Payload\CreateSubscriptionPayload;
use Webtolk\Max\Payload\EditMessageBody;
use Webtolk\Max\Payload\NewCommentBody;
use Webtolk\Max\Payload\NewMessageBody;
use Webtolk\Max\Payload\PinChatMessagePayload;
use Webtolk\Max\Payload\SenderAction;
use Webtolk\Max\Payload\UpdateChatPayload;
use Webtolk\Max\Payload\UploadType;
use Webtolk\Max\Query\ChatMembersQuery;
use Webtolk\Max\Query\CommentQuery;
use Webtolk\Max\Query\GetUpdatesQuery;
use Webtolk\Max\Query\MessageQuery;

/** Opt-in network scenarios; raw output must remain private. */
final class ContractRunner
{
    /** @var array<string, array<string, mixed>> */
    private array $cases = [];
    /** @var list<string> */
    private array $messages = [];
    /** @var list<array{string, string}> */
    private array $comments = [];
    /** @var list<string> */
    private array $getterFailures = [];
    private bool $pinTouched = false;
    private ?string $oldPin = null;
    private string $runId;
    private string $subscriptionUrl;

    /** @param array<string, mixed> $params */
    public function __construct(
        private readonly Max $sdk,
        private readonly RecordingClient $client,
        private readonly array $params,
        private readonly string $transport,
        private readonly string $output,
    ) {
        $this->runId = gmdate('Ymd-His') . '-' . $transport . '-' . bin2hex(random_bytes(3));
        $this->subscriptionUrl = 'https://joomla.local/index.php?option=com_ajax&plugin=wtmax&group=system&format=json&action=webhook&sdk_test=' . $this->runId;
    }

    /** @return array<string, mixed> */
    public function run(): array
    {
        $sdk = $this->sdk;
        $chatId = (int)$this->params['default_chat_id'];
        $before = [];
        try {
            $bot = $this->invoke('bots.me', fn () => $sdk->bots()->me());
            $chat = $this->invoke('chats.getById', fn () => $sdk->chats()->getById($chatId));
            $subscriptions = $this->invoke('subscriptions.list', fn () => $sdk->subscriptions()->list());
            $before = ['bot' => $bot?->toArray(), 'chat' => $chat?->toArray(), 'subscriptions' => $subscriptions?->toArray()];
            $this->save('baseline.json', $before);
            if ($bot === null || $chat === null) {
                throw new \RuntimeException('Test identity/chat preflight failed; mutations were not started.');
            }
            $botId = (int)($bot->toArray()['user_id'] ?? 0);
            $ownerId = (int)($chat->toArray()['owner_id'] ?? 0);
            $commands = [];
            foreach ($bot->getCommands() as $command) {
                $name = $command->getName() ?? $command->getCommand();
                if ($name !== null && $command->getDescription() !== null) {
                    $commands[] = BotCommandPayload::create($name, $command->getDescription());
                }
            }
            $this->invoke('bots.updateCommands', fn () => $sdk->bots()->updateCommands(BotCommandsPayload::create(...$commands)), 'same_observed_commands');
            $list = $this->invoke('chats.list', fn () => $sdk->chats()->list(count: 100), 'legacy_endpoint');
            $this->invoke('chats.getByLink', fn () => $sdk->chats()->getByLink((string)$chat->getLink()), 'legacy_endpoint');
            $this->invoke('chats.members', fn () => $sdk->chats()->members($chatId, ChatMembersQuery::page(count: 10)));
            $this->invoke('chats.memberMe', fn () => $sdk->chats()->memberMe($chatId));
            $this->invoke('chats.admins', fn () => $sdk->chats()->admins($chatId));
            $update = (new UpdateChatPayload())->withTitle((string)$chat->getTitle());
            if ($chat->getDescription() !== null) {
                $update->withDescription($chat->getDescription());
            }
            $this->invoke('chats.update', fn () => $sdk->chats()->update($chatId, $update), 'same_observed_settings');
            $pinned = $this->invoke('chats.getPinnedMessage', fn () => $sdk->chats()->getPinnedMessage($chatId));
            $this->oldPin = $pinned instanceof Message ? self::messageId($pinned) : null;

            // Invalid chat 0 cannot remove an existing chat, bot, member or administrator.
            $this->invoke('chats.delete', fn () => $sdk->chats()->delete(0), 'negative_invalid_chat');
            $this->invoke('chats.leave', fn () => $sdk->chats()->leave(0), 'negative_invalid_chat');
            $this->invoke('chats.addMembers', fn () => $sdk->chats()->addMembers(0, AddChatMembersPayload::create($botId)), 'negative_invalid_chat');
            $this->invoke('chats.removeMember', fn () => $sdk->chats()->removeMember(0, $botId, false), 'negative_invalid_chat');
            $this->invoke('chats.addAdmins', fn () => $sdk->chats()->addAdmins(0, AddChatAdminsPayload::create(ChatAdminAssignment::forUser($botId)->withPermissions('read_all_messages'))), 'negative_invalid_chat');
            $this->invoke('chats.removeAdmin', fn () => $sdk->chats()->removeAdmin(0, $botId), 'negative_invalid_chat');
            $this->invoke('chats.sendAction', fn () => $sdk->chats()->sendAction($chatId, SenderAction::TYPING_ON));

            $text = '[MAX SDK 0.4.0 contract ' . $this->runId . ']';
            $message = $this->invoke('messages.sendToChat', fn () => $sdk->messages()->sendToChat($chatId, NewMessageBody::text($text)->withNotify(false), true));
            $mid = $message instanceof Message ? $this->remember($message) : null;
            if ($ownerId > 0) {
                $direct = $this->invoke('messages.sendToUser', fn () => $sdk->messages()->sendToUser($ownerId, NewMessageBody::text($text)->withNotify(false), true), 'observed_test_chat_owner');
                if ($direct instanceof Message) {
                    $this->remember($direct);
                }
            } else {
                $this->invoke('messages.sendToUser', fn () => $sdk->messages()->sendToUser($botId, NewMessageBody::text($text)), 'negative_bot_recipient');
            }
            if ($mid !== null) {
                $this->invoke('messages.getById', fn () => $sdk->messages()->getById($mid));
                $this->invoke('messages.getByQueryId', fn () => $sdk->messages()->getByQueryId($mid));
                $this->invoke('messages.edit', fn () => $sdk->messages()->edit($mid, EditMessageBody::text($text . ' edited')));
                $this->pinTouched = true;
                $this->invoke('chats.pin', fn () => $sdk->chats()->pin($chatId, PinChatMessagePayload::create($mid)->withNotify(false)));
                $this->invoke('chats.unpin', fn () => $sdk->chats()->unpin($chatId));
            }
            $this->invoke('messages.list', fn () => $sdk->messages()->list(MessageQuery::forChat($chatId)->withCount(10)));
            $button = InlineKeyboardAttachment::rows([CallbackButton::create('SDK test', 'sdk-contract-' . $this->runId)]);
            $keyboard = $this->invoke('messages.sendToChat.keyboard', fn () => $sdk->messages()->sendToChat($chatId, NewMessageBody::text($text)->withNotify(false)->withAttachments([$button])));
            if ($keyboard instanceof Message) {
                $this->remember($keyboard);
            }
            $this->invoke('messages.answerCallback', fn () => $sdk->messages()->answerCallback('sdk-unissued-' . $this->runId, (new CallbackAnswerPayload())->withNotification('SDK contract test'), true), 'negative_unissued_callback');

            $upload = $this->invoke('uploads.create', fn () => $sdk->uploads()->create(UploadType::FILE));
            if ($upload instanceof UploadUrl) {
                $this->invoke('uploads.pushBinary', fn () => $sdk->uploads()->pushBinary($upload, "MAX SDK contract test\n", 'text/plain'));
            }
            $file = $this->invoke('uploads.upload', fn () => $sdk->uploads()->upload(UploadType::FILE, "MAX SDK contract test\n", 'text/plain'));
            if ($file !== null && $file->getToken() !== null) {
                $attachment = FileAttachment::fromToken($file->getToken());
                $sent = $this->invoke('messages.sendToChat.file', fn () => $this->retryAttachment(fn () => $sdk->messages()->sendToChat($chatId, NewMessageBody::text($text)->withNotify(false)->withAttachments([$attachment]))));
                if ($sent instanceof Message) {
                    $this->remember($sent);
                }
            }
            $video = $this->invoke('uploads.create.video', fn () => $sdk->uploads()->create(UploadType::VIDEO));
            $videoToken = $video?->getToken();
            $this->invoke('uploads.getVideo', fn () => $sdk->uploads()->getVideo($videoToken ?? 'sdk-unissued-video-' . $this->runId), $videoToken === null ? 'negative_unissued_video' : 'reserved_video_token');

            $commentChat = $chatId;
            if (!empty($this->params['test_channel_id'])) {
                $candidate = $this->invoke('chats.getById.testChannel', fn () => $sdk->chats()->getById((int)$this->params['test_channel_id']));
                if ($candidate?->getType() === 'channel' && mb_strtolower(trim((string)$candidate->getTitle())) === 'webtolk test channel') {
                    $commentChat = (int)$candidate->getId();
                }
            }
            foreach ($list?->getChats() ?? [] as $candidate) {
                if ($candidate->getType() === 'channel' && mb_strtolower(trim((string)$candidate->getTitle())) === 'webtolk test channel') {
                    $commentChat = (int)$candidate->getId();
                    break;
                }
            }
            $parent = $this->invoke('messages.sendToChat.commentParent', fn () => $sdk->messages()->sendToChat($commentChat, NewMessageBody::text($text . ' comment parent')));
            $parentId = $parent instanceof Message ? $this->remember($parent) : $mid;
            if ($parentId !== null) {
                $comment = $this->invoke('messages.sendComment', fn () => $sdk->messages()->sendComment($parentId, NewCommentBody::text($text), true));
                $raw = $comment?->toArray() ?? [];
                $commentId = $raw['id'] ?? $raw['comment_id'] ?? $raw['mid'] ?? $raw['body']['mid'] ?? null;
                if (is_string($commentId)) {
                    $this->comments[] = [$parentId, $commentId];
                }
                $this->invoke('messages.listComments', fn () => $sdk->messages()->listComments($parentId, CommentQuery::all()->withCount(10)));
                $commentId = is_string($commentId) ? $commentId : 'sdk-unissued-comment-' . $this->runId;
                $kind = str_starts_with($commentId, 'sdk-unissued-') ? 'negative_unissued_comment' : 'positive';
                $this->invoke('messages.getComment', fn () => $sdk->messages()->getComment($parentId, $commentId), $kind);
                $this->invoke('messages.editComment', fn () => $sdk->messages()->editComment($parentId, $commentId, NewCommentBody::text($text . ' edited')), $kind);
                $deleted = $this->invoke('messages.deleteComment', fn () => $sdk->messages()->deleteComment($parentId, $commentId), $kind);
                if ($deleted instanceof OperationResult && $deleted->isSuccess()) {
                    $this->comments = [];
                }
            }
            $this->invoke('updates.list', fn () => $sdk->updates()->list(GetUpdatesQuery::default()->withLimit(1)->withTimeout(0)->withTypes('comment_created', 'comment_edited', 'comment_removed')));
            $this->invoke('subscriptions.create', fn () => $sdk->subscriptions()->create(CreateSubscriptionPayload::create($this->subscriptionUrl, ['bot_admin_permissions_changed'], (string)$this->params['webhook_secret'])), 'owned_local_webhook_registration');
            $this->invoke('subscriptions.list.afterCreate', fn () => $sdk->subscriptions()->list());
            $this->invoke('subscriptions.delete', fn () => $sdk->subscriptions()->delete($this->subscriptionUrl));
        } finally {
            foreach ($this->comments as [$parentId, $commentId]) {
                $this->invoke('messages.deleteComment.cleanup', fn () => $sdk->messages()->deleteComment($parentId, $commentId), 'cleanup');
            }
            if ($this->pinTouched) {
                $this->invoke('chats.restorePin', fn () => $this->oldPin !== null
                    ? $sdk->chats()->pin($chatId, PinChatMessagePayload::create($this->oldPin)->withNotify(false))
                    : $sdk->chats()->unpin($chatId), 'cleanup');
            }
            foreach (array_reverse(array_unique($this->messages)) as $index => $id) {
                $this->invoke($index === 0 ? 'messages.delete' : 'messages.delete.cleanup' . $index, fn () => $sdk->messages()->delete($id), 'cleanup');
            }
            $this->invoke('subscriptions.delete.cleanup', fn () => $sdk->subscriptions()->delete($this->subscriptionUrl), 'cleanup');
            $afterSubscriptions = $this->invoke('subscriptions.list.afterCleanup', fn () => $sdk->subscriptions()->list());
            $afterChat = $this->invoke('chats.getById.afterCleanup', fn () => $sdk->chats()->getById($chatId));
            $this->save('after-cleanup.json', ['chat' => $afterChat?->toArray(), 'subscriptions' => $afterSubscriptions?->toArray()]);
            $this->save('summary.json', ['transport' => $this->transport, 'run_id' => $this->runId, 'stand' => 'joomla.local',
                'cases' => $this->cases, 'getter_failures' => $this->getterFailures,
                'created_message_count' => count(array_unique($this->messages)), 'baseline_available' => $before !== []]);
        }

        if ($this->getterFailures !== []) {
            throw new \RuntimeException('A public entity getter failed; inspect private evidence.');
        }

        return $this->cases;
    }

    /** @return array<string, array<string, mixed>> */
    public function runComments(): array
    {
        $channel = $this->invoke('chats.getById.testChannel', fn () => $this->sdk->chats()->getById((int)($this->params['test_channel_id'] ?? 0)));
        if ($channel?->getType() !== 'channel' || mb_strtolower(trim((string)$channel->getTitle())) !== 'webtolk test channel') {
            throw new \RuntimeException('The saved comment test-channel identity was not verified.');
        }
        try {
            // MAX rejects notify=false for channel posts (errors.send-message.channel-notify).
            $parent = $this->invoke('messages.sendToChat.commentParent', fn () => $this->sdk->messages()->sendToChat((int)$channel->getId(), NewMessageBody::text('[MAX SDK comment contract ' . $this->runId . ']')));
            $parentId = $parent instanceof Message ? $this->remember($parent) : null;
            if ($parentId === null) {
                throw new \RuntimeException('The comment parent post was not created.');
            }
            $comment = $this->invoke('messages.sendComment', fn () => $this->sdk->messages()->sendComment($parentId, NewCommentBody::text('SDK contract comment'), true));
            $raw = $comment?->toArray() ?? [];
            $commentId = $raw['id'] ?? $raw['comment_id'] ?? $raw['mid'] ?? $raw['body']['mid'] ?? null;
            if (!is_string($commentId)) {
                throw new \RuntimeException('The comment was not created.');
            }
            $this->comments[] = [$parentId, $commentId];
            $this->invoke('messages.listComments', fn () => $this->sdk->messages()->listComments($parentId, CommentQuery::all()->withCount(10)));
            $this->invoke('messages.getComment', fn () => $this->sdk->messages()->getComment($parentId, $commentId));
            $this->invoke('messages.editComment', fn () => $this->sdk->messages()->editComment($parentId, $commentId, NewCommentBody::text('SDK contract comment edited')));
            $this->invoke('messages.getComment.afterEdit', fn () => $this->sdk->messages()->getComment($parentId, $commentId));
            $deleted = $this->invoke('messages.deleteComment', fn () => $this->sdk->messages()->deleteComment($parentId, $commentId));
            if ($deleted instanceof OperationResult && $deleted->isSuccess()) {
                $this->comments = [];
            }
            $this->invoke('messages.listComments.afterDelete', fn () => $this->sdk->messages()->listComments($parentId, CommentQuery::all()->withCount(10)));
        } finally {
            foreach ($this->comments as [$parentId, $commentId]) {
                $this->invoke('messages.deleteComment.cleanup', fn () => $this->sdk->messages()->deleteComment($parentId, $commentId), 'cleanup');
            }
            foreach ($this->messages as $id) {
                $this->invoke('messages.delete.parent', fn () => $this->sdk->messages()->delete($id), 'cleanup');
            }
            $this->save('summary.json', ['transport' => $this->transport, 'run_id' => $this->runId,
                'cases' => $this->cases, 'getter_failures' => $this->getterFailures]);
        }

        if ($this->getterFailures !== []) {
            throw new \RuntimeException('A public entity getter failed; inspect private evidence.');
        }

        return $this->cases;
    }

    private function invoke(string $name, callable $callback, string $scenario = 'positive'): mixed
    {
        $value = null;
        $case = ['scenario' => $scenario, 'outcome' => 'success'];
        try {
            $value = $callback();
            $this->observe($value);
            $case['result'] = $this->normalize($value);
            if ($value instanceof OperationResult && !$value->isSuccess()) {
                $case['outcome'] = 'operation_rejected';
            }
        } catch (ApiException $error) {
            $case += ['error_class' => $error::class, 'http_status' => $error->statusCode, 'api_code' => $error->apiCode];
            $case['outcome'] = 'api_error';
        } catch (Throwable $error) {
            $case += ['error_class' => $error::class, 'error' => $error->getMessage()];
            $case['outcome'] = 'unexpected_error';
        }
        $case['http'] = $this->client->flush();
        $case['transport'] = $this->transport;
        $case['case'] = $name;
        $this->save($name . '.json', $case);
        $this->cases[$name] = ['scenario' => $scenario, 'outcome' => $case['outcome'], 'requests' => count($case['http']),
            'http_status' => $case['http_status'] ?? ($case['http'] !== [] ? end($case['http'])['status'] ?? null : null), 'api_code' => $case['api_code'] ?? null];
        $this->save('progress.json', $this->cases);
        echo $this->transport . ' ' . $name . ' ' . $case['outcome'] . PHP_EOL;
        usleep(100000);

        return $value;
    }

    private function observe(mixed $value, int $depth = 0): void
    {
        if ($depth > 5) {
            return;
        }
        if (is_array($value)) {
            foreach (array_slice($value, 0, 20) as $child) {
                $this->observe($child, $depth + 1);
            }
        } elseif ($value instanceof AbstractEntity) {
            foreach ((new \ReflectionObject($value))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->isConstructor() || $method->getNumberOfRequiredParameters() > 0 || $method->getName() === 'jsonSerialize' || $method->getName() === 'toArray') {
                    continue;
                }
                if (!preg_match('/^(get|is|has)[A-Z]/', $method->getName())) {
                    continue;
                }
                try {
                    $this->observe($method->invoke($value), $depth + 1);
                } catch (Throwable $error) {
                    $this->getterFailures[] = $method->getDeclaringClass()->getName() . '::' . $method->getName() . ':' . $error::class;
                }
            }
            json_encode($value, JSON_THROW_ON_ERROR);
        }
    }

    private function normalize(mixed $value): mixed
    {
        return $value instanceof JsonSerializable ? $value->jsonSerialize() : $value;
    }

    private function remember(Message $message): ?string
    {
        $id = self::messageId($message);
        if ($id !== null) {
            $this->messages[] = $id;
        }

        return $id;
    }

    private static function messageId(Message $message): ?string
    {
        $raw = $message->toArray();
        $id = $raw['body']['mid'] ?? $raw['mid'] ?? $raw['message_id'] ?? null;

        return is_string($id) && $id !== '' ? $id : null;
    }

    private function retryAttachment(callable $callback): mixed
    {
        for ($attempt = 0; ; ++$attempt) {
            try {
                return $callback();
            } catch (AttachmentNotReadyException $error) {
                if ($attempt >= 4) {
                    throw $error;
                }
                usleep(500000);
            }
        }
    }

    private function save(string $file, mixed $value): void
    {
        file_put_contents($this->output . '/' . $file, json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
}
