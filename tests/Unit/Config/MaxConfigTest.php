<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Config;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Config\MaxConfig;

final class MaxConfigTest extends TestCase
{
    public function testUsesDefaultBaseUriConstant(): void
    {
        $config = new MaxConfig('token');

        $this->assertSame('token', $config->getToken());
        $this->assertSame('https://platform-api2.max.ru', MaxConfig::DEFAULT_BASE_URI);
    }

    public function testPreservesDefaultHeaders(): void
    {
        $config = new MaxConfig('token', ['X-Test' => '1']);

        $this->assertSame(['X-Test' => '1'], $config->getDefaultHeaders());
    }

    public function testPreservesEmptyDefaultHeaders(): void
    {
        $config = new MaxConfig('token');

        $this->assertSame([], $config->getDefaultHeaders());
    }
}
