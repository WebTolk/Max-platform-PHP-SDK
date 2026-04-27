<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit;

use InvalidArgumentException;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Webtolk\Max\Config\MaxConfig;
use Webtolk\Max\Module\BotModule;
use Webtolk\Max\Module\ChatModule;
use Webtolk\Max\Max;
use Webtolk\Max\Module\MessageModule;
use Webtolk\Max\Module\SubscriptionModule;
use Webtolk\Max\Module\UploadModule;
use Webtolk\Max\Module\UpdateModule;

final class MaxTest extends TestCase
{
    public function testModulesRequireConfiguredHttpClient(): void
    {
        $sdk = new Max(new MaxConfig('token'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Transport is not configured. Call setTransport() first.');

        $sdk->bots();
    }

    public function testModulesAreLazilyInitializedAndMemoized(): void
    {
        $sdk = new Max(new MaxConfig('token'));
        $sdk->setTransport(
            $this->createMock(ClientInterface::class),
            new RequestFactory(),
            new StreamFactory(),
        );

        $this->assertInstanceOf(BotModule::class, $sdk->bots());
        $this->assertInstanceOf(ChatModule::class, $sdk->chats());
        $this->assertInstanceOf(MessageModule::class, $sdk->messages());
        $this->assertInstanceOf(SubscriptionModule::class, $sdk->subscriptions());
        $this->assertInstanceOf(UploadModule::class, $sdk->uploads());
        $this->assertInstanceOf(UpdateModule::class, $sdk->updates());

        $this->assertSame($sdk->messages(), $sdk->messages());
        $this->assertSame($sdk->chats(), $sdk->chats());
        $this->assertSame($sdk->bots(), $sdk->bots());
        $this->assertSame($sdk->uploads(), $sdk->uploads());
        $this->assertSame($sdk->subscriptions(), $sdk->subscriptions());
        $this->assertSame($sdk->updates(), $sdk->updates());
    }

    public function testConfigOnlyConstructorWorksAfterSetTransport(): void
    {
        $sdk = new Max(new MaxConfig('token'));

        $this->assertSame(
            $sdk,
            $sdk->setTransport(
                $this->createMock(ClientInterface::class),
                new RequestFactory(),
                new StreamFactory(),
            ),
        );
        $this->assertInstanceOf(BotModule::class, $sdk->bots());
        $this->assertInstanceOf(ChatModule::class, $sdk->chats());
    }

    public function testSetTransportRebuildsClientAndInvalidatesModules(): void
    {
        $sdk = new Max(new MaxConfig('token'));
        $sdk->setTransport(
            $this->createMock(ClientInterface::class),
            new RequestFactory(),
            new StreamFactory(),
        );

        $botsModule = $sdk->bots();
        $messagesModule = $sdk->messages();

        $this->assertSame(
            $sdk,
            $sdk->setTransport(
                $this->createMock(ClientInterface::class),
                new RequestFactory(),
                new StreamFactory(),
            ),
        );

        $this->assertNotSame($botsModule, $sdk->bots());
        $this->assertNotSame($messagesModule, $sdk->messages());
    }

    public function testSetLoggerKeepsModulesAndUpdatesSharedTransport(): void
    {
        $sdk = new Max(new MaxConfig('token'));
        $sdk->setTransport(
            $this->createMock(ClientInterface::class),
            new RequestFactory(),
            new StreamFactory(),
        );

        $botsModule = $sdk->bots();

        $logger = $this->createMock(LoggerInterface::class);

        $this->assertSame($sdk, $sdk->setLogger($logger));
        $this->assertSame($botsModule, $sdk->bots());
    }
}
