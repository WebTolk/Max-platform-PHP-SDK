<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Request;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Interface\ApiTransportInterface;
use Webtolk\Max\Entity\UploadUrl;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Payload\UploadType;
use Webtolk\Max\Request\UploadRequest;
use Webtolk\Max\Tests\Unit\Support\ResponseFactoryTrait;

final class UploadRequestTest extends TestCase
{
    use ResponseFactoryTrait;

    public function testCreateReturnsUploadUrl(): void
    {
        $response = $this->createJsonResponse($this, json_encode([
            'url' => 'https://upload.test/endpoint?type=video',
            'token' => 'slot-token',
            'type' => 'video',
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('POST', '/uploads', ['type' => 'video'])
            ->willReturn($response);

        $request = new UploadRequest($httpClient);
        $result = $request->create(UploadType::VIDEO);

        $this->assertSame(UploadType::VIDEO, $result->getType());
        $this->assertSame('slot-token', $result->getToken());
    }

    public function testUploadDelegatesToCreateAndPushBinary(): void
    {
        $createResponse = $this->createJsonResponse($this, json_encode([
            'url' => 'https://upload.test/endpoint?type=video',
            'token' => 'slot-token',
        ]));
        $pushResponse = $this->createResponse($this, '<retval>1</retval>');

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('POST', '/uploads', ['type' => UploadType::VIDEO->value])
            ->willReturn($createResponse);
        $httpClient->expects($this->once())
            ->method('requestBinary')
            ->with(
                'https://upload.test/endpoint?type=video',
                $this->callback(function (string $body): bool {
                    $this->assertStringContainsString('Content-Disposition: form-data; name="data"; filename="upload.mp4"', $body);
                    $this->assertStringContainsString('Content-Type: video/mp4', $body);
                    $this->assertStringContainsString('payload-bytes', $body);

                    return true;
                }),
                $this->callback(static fn(string $contentType): bool => str_starts_with($contentType, 'multipart/form-data; boundary=')),
            )
            ->willReturn($pushResponse);

        $request = new UploadRequest($httpClient);
        $result = $request->upload(UploadType::VIDEO, 'payload-bytes');

        $this->assertSame(UploadType::VIDEO, $result->getType());
        $this->assertSame('slot-token', $result->getToken());
        $this->assertSame('1', $result->toArray()['retval']);
    }

    public function testPushBinaryExtractsNestedImageTokenFromMultipartUploadResponse(): void
    {
        $pushResponse = $this->createJsonResponse($this, json_encode([
            'photos' => [
                'abc123' => [
                    'token' => 'image-token',
                ],
            ],
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestBinary')
            ->with(
                'https://upload.test/endpoint?type=image',
                $this->callback(function (string $body): bool {
                    $this->assertStringContainsString('Content-Disposition: form-data; name="data"; filename="upload.jpg"', $body);
                    $this->assertStringContainsString('Content-Type: image/jpeg', $body);
                    $this->assertStringContainsString('image-bytes', $body);

                    return true;
                }),
                $this->callback(static fn(string $contentType): bool => str_starts_with($contentType, 'multipart/form-data; boundary=')),
            )
            ->willReturn($pushResponse);

        $request = new UploadRequest($httpClient);
        $result = $request->pushBinary(
            new UploadUrl(['url' => 'https://upload.test/endpoint?type=image'], UploadType::IMAGE),
            'image-bytes',
            'image/jpeg',
        );

        $this->assertSame(UploadType::IMAGE, $result->getType());
        $this->assertSame('image-token', $result->getToken());
    }

    public function testPushBinaryThrowsWhenUploadUrlObjectHasEmptyTargetUrl(): void
    {
        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->never())->method('requestBinary');

        $request = new UploadRequest($httpClient);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Upload target URL is missing.');

        $request->pushBinary(
            new UploadUrl([], UploadType::FILE),
            'payload',
            'text/plain',
        );
    }

    public function testUploadThrowsWhenCreateResponseMissesUrl(): void
    {
        $createResponse = $this->createJsonResponse($this, json_encode([
            'token' => 'slot-token',
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('POST', '/uploads', ['type' => UploadType::FILE->value])
            ->willReturn($createResponse);

        $request = new UploadRequest($httpClient);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Upload URL is missing in /uploads response.');

        $request->upload(UploadType::FILE, 'payload');
    }

    public function testPushBinaryWithStringTargetInfersFileTypeFromUrlWhenMissingType(): void
    {
        $pushResponse = $this->createJsonResponse($this, json_encode([]));
        $uploadUrl = 'https://upload.test/endpoint';

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestBinary')
            ->with(
                $uploadUrl,
                $this->callback(function (string $body): bool {
                    $this->assertStringContainsString('Content-Disposition: form-data; name="data"; filename="upload.txt"', $body);
                    $this->assertStringContainsString('Content-Type: text/plain', $body);
                    $this->assertStringContainsString('x', $body);

                    return true;
                }),
                $this->callback(static fn(string $contentType): bool => str_starts_with($contentType, 'multipart/form-data; boundary=')),
            )
            ->willReturn($pushResponse);

        $request = new UploadRequest($httpClient);
        $result = $request->pushBinary($uploadUrl, 'x', 'text/plain');

        $this->assertSame(UploadType::FILE, $result->getType());
    }

    public function testPushBinaryWrapsScalarJsonResponse(): void
    {
        $pushResponse = $this->createResponse($this, '1');

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestBinary')
            ->willReturn($pushResponse);

        $request = new UploadRequest($httpClient);
        $result = $request->pushBinary('https://upload.test/endpoint', 'x', 'text/plain');

        $this->assertSame([
            'value' => 1,
            'raw_body' => '1',
        ], $result->toArray());
    }

    public function testPushBinaryWrapsPlainTextResponse(): void
    {
        $pushResponse = $this->createResponse($this, 'plain upload response');

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestBinary')
            ->willReturn($pushResponse);

        $request = new UploadRequest($httpClient);
        $result = $request->pushBinary('https://upload.test/endpoint', 'x', 'text/plain');

        $this->assertSame([
            'raw_body' => 'plain upload response',
        ], $result->toArray());
    }
}
