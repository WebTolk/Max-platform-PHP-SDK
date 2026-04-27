<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Entity\Video;

final class VideoTest extends TestCase
{
    public function testVideoExposesTypedMetadata(): void
    {
        $video = new Video([
            'token' => 'video-token',
            'urls' => [
                'mp4' => 'https://cdn.example.test/video.mp4',
            ],
            'thumbnail' => [
                'url' => 'https://cdn.example.test/thumb.jpg',
            ],
            'width' => '1920',
            'height' => 1080,
            'duration' => '42',
        ]);

        $this->assertSame('video-token', $video->getToken());
        $this->assertSame(['mp4' => 'https://cdn.example.test/video.mp4'], $video->getUrls());
        $this->assertSame(['url' => 'https://cdn.example.test/thumb.jpg'], $video->getThumbnail());
        $this->assertSame(1920, $video->getWidth());
        $this->assertSame(1080, $video->getHeight());
        $this->assertSame(42, $video->getDuration());
    }

    public function testVideoReturnsNullForMissingOptionalFields(): void
    {
        $video = new Video([
            'token' => 'video-token',
        ]);

        $this->assertSame('video-token', $video->getToken());
        $this->assertNull($video->getUrls());
        $this->assertNull($video->getThumbnail());
        $this->assertNull($video->getWidth());
        $this->assertNull($video->getHeight());
        $this->assertNull($video->getDuration());
    }
}
