<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Payload;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Payload\CreateSubscriptionPayload;

final class CreateSubscriptionPayloadTest extends TestCase
{
    public function testBuildsRequestArrayWithAllFields(): void
    {
        $payload = CreateSubscriptionPayload::create(
            'https://example.com/hook',
            ['message', 'message', 'callback'],
            'sec_ret',
        );

        $this->assertSame([
            'url' => 'https://example.com/hook',
            'update_types' => ['message_created', 'message_callback'],
            'secret' => 'sec_ret',
        ], $payload->toRequestArray());
    }

    public function testCreatesPayloadWithoutOptionalFields(): void
    {
        $payload = CreateSubscriptionPayload::create('https://example.com/hook');

        $this->assertSame([
            'url' => 'https://example.com/hook',
        ], $payload->toRequestArray());
    }

    public function testInvalidUrlRejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('URL must start with http(s)://');

        CreateSubscriptionPayload::create('ftp://example.com/hook');
    }

    public function testInvalidSecretRejected(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Secret must match pattern: 5..256 alphanumeric chars and _-.');

        CreateSubscriptionPayload::create('https://example.com/hook', [], 'a-b');
    }
}
