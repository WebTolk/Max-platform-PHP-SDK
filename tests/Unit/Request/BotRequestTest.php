<?php

declare(strict_types=1);

namespace Webtolk\Max\Tests\Unit\Request;

use PHPUnit\Framework\TestCase;
use Webtolk\Max\Interface\ApiTransportInterface;
use Webtolk\Max\Payload\BotCommandPayload;
use Webtolk\Max\Payload\BotCommandsPayload;
use Webtolk\Max\Request\BotRequest;
use Webtolk\Max\Tests\Unit\Support\ResponseFactoryTrait;

final class BotRequestTest extends TestCase
{
    use ResponseFactoryTrait;

    public function testMeUsesGetEndpointAndHydratesBotInfo(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'user_id' => 7,
            'first_name' => 'Bot',
            'is_bot' => true,
        ]));

        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())
            ->method('requestJson')
            ->with('GET', '/me')
            ->willReturn($response);

        $request = new BotRequest($httpClient);
        $result = $request->me();

        $this->assertSame(7, $result->getId());
        $this->assertSame('Bot', $result->getFirstName());
    }

    public function testUpdateCommandsUsesPatchEndpointAndHydratesList(): void
    {
        $response = $this->createJsonResponse($this, $this->encodeJson([
            'commands' => [['name' => 'help', 'description' => 'Помощь']],
        ]));
        $httpClient = $this->createMock(ApiTransportInterface::class);
        $httpClient->expects($this->once())->method('requestJson')->with(
            'PATCH',
            '/me/commands',
            [],
            [],
            ['commands' => [['name' => 'help', 'description' => 'Помощь']]],
        )->willReturn($response);

        $result = (new BotRequest($httpClient))->updateCommands(
            BotCommandsPayload::create(BotCommandPayload::create('help', 'Помощь')),
        );

        $this->assertSame('help', $result->getCommands()[0]->getName());
    }
}
