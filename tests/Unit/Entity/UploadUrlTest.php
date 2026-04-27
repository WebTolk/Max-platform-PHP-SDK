<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Entity\UploadUrl;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Payload\UploadType;

final class UploadUrlTest extends TestCase
{
    public function testRequireTrustedUrlReturnsTrustedHttpsUploadHost(): void
    {
        $uploadUrl = new UploadUrl([
            'url' => 'https://fu.oneme.ru/api/upload.do?sig=abc',
            'token' => 'token',
        ], UploadType::FILE);

        $this->assertSame('https://fu.oneme.ru/api/upload.do?sig=abc', $uploadUrl->requireTrustedUrl());
    }

    public function testConstructorRejectsNonHttpsUploadUrl(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Upload URL must use HTTPS.');

        new UploadUrl([
            'url' => 'http://fu.oneme.ru/api/upload.do?sig=abc',
        ], UploadType::FILE);
    }

    public function testConstructorRejectsUntrustedUploadHost(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Upload URL host is not trusted.');

        new UploadUrl([
            'url' => 'https://evil.example/upload.do?sig=abc',
        ], UploadType::FILE);
    }

    public function testRequireTrustedUrlThrowsWhenUrlIsMissing(): void
    {
        $uploadUrl = new UploadUrl([], UploadType::FILE);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Upload target URL is missing.');

        $uploadUrl->requireTrustedUrl();
    }
}
