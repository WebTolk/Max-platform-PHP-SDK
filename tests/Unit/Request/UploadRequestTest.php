<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Request;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Entity\UploadUrl;
use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Interface\ApiTransportInterface;
use Webtolk\Max\Payload\UploadType;
use Webtolk\Max\Request\UploadRequest;
use Webtolk\Max\Tests\Unit\Support\ResponseFactoryTrait;

final class UploadRequestTest extends TestCase
{
    use ResponseFactoryTrait;

    public function testCreateReturnsUploadUrl(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'url' => 'https://vu.okcdn.ru/upload.do?type=video',
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

    public function testGetVideoUsesVideoLookupEndpoint(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'token' => 'video-token',
            'urls' => [
                'mp4' => 'https://cdn.example.test/video.mp4',
            ],
            'thumbnail' => [
                'url' => 'https://cdn.example.test/thumb.jpg',
            ],
            'width' => 1920,
            'height' => 1080,
            'duration' => 42,
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('GET', '/videos/video-token')
            ->willReturn($response);

        $request = new UploadRequest($httpClient);
        $video = $request->getVideo('video-token');

        $this->assertSame('video-token', $video->getToken());
        $this->assertSame(['mp4' => 'https://cdn.example.test/video.mp4'], $video->getUrls());
        $this->assertSame(['url' => 'https://cdn.example.test/thumb.jpg'], $video->getThumbnail());
        $this->assertSame(1920, $video->getWidth());
        $this->assertSame(1080, $video->getHeight());
        $this->assertSame(42, $video->getDuration());
    }

    public function testUploadDelegatesToCreateAndPushBinary(): void
    {
        $createResponse = $this->createJsonResponse($this, $this->encodeJson([
            'url' => 'https://vu.okcdn.ru/upload.do?type=video',
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
                'https://vu.okcdn.ru/upload.do?type=video',
                $this->callback(function (string $body): bool {
                    $this->assertStringContainsString('Content-Disposition: form-data; name="data"; filename="upload.mp4"', $body);
                    $this->assertStringContainsString('Content-Type: video/mp4', $body);
                    $this->assertStringContainsString('payload-bytes', $body);

                    return true;
                }),
                $this->callback(static fn (string $contentType): bool => str_starts_with($contentType, 'multipart/form-data; boundary=')),
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
        $pushResponse = $this->createJsonResponse($this, $this->encodeJson([
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
                'https://fu.oneme.ru/api/upload.do?type=image',
                $this->callback(function (string $body): bool {
                    $this->assertStringContainsString('Content-Disposition: form-data; name="data"; filename="upload.jpg"', $body);
                    $this->assertStringContainsString('Content-Type: image/jpeg', $body);
                    $this->assertStringContainsString('image-bytes', $body);

                    return true;
                }),
                $this->callback(static fn (string $contentType): bool => str_starts_with($contentType, 'multipart/form-data; boundary=')),
            )
            ->willReturn($pushResponse);

        $request = new UploadRequest($httpClient);
        $result = $request->pushBinary(
            new UploadUrl(['url' => 'https://fu.oneme.ru/api/upload.do?type=image'], UploadType::IMAGE),
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
        $createResponse = $this->createJsonResponse($this, $this->encodeJson([
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

    public function testPushBinaryWrapsScalarJsonResponse(): void
    {
        $pushResponse = $this->createResponse($this, '1');

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestBinary')
            ->with(
                'https://fu.oneme.ru/api/upload.do?type=file',
                $this->anything(),
                $this->anything(),
            )
            ->willReturn($pushResponse);

        $request = new UploadRequest($httpClient);
        $result = $request->pushBinary(
            new UploadUrl(['url' => 'https://fu.oneme.ru/api/upload.do?type=file'], UploadType::FILE),
            'x',
            'text/plain',
        );

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
            ->with(
                'https://fu.oneme.ru/api/upload.do?type=file',
                $this->anything(),
                $this->anything(),
            )
            ->willReturn($pushResponse);

        $request = new UploadRequest($httpClient);
        $result = $request->pushBinary(
            new UploadUrl(['url' => 'https://fu.oneme.ru/api/upload.do?type=file'], UploadType::FILE),
            'x',
            'text/plain',
        );

        $this->assertSame([
            'raw_body' => 'plain upload response',
        ], $result->toArray());
    }

    public function testCreateRejectsUntrustedUploadHost(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'url' => 'https://evil.example/upload.do?type=file',
            'token' => 'slot-token',
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->willReturn($response);

        $request = new UploadRequest($httpClient);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Upload URL host is not trusted.');

        $request->create(UploadType::FILE);
    }
}
