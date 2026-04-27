<?php

declare(strict_types=1);

namespace Webtolk\Max;

use InvalidArgumentException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Webtolk\Max\Config\MaxConfig;
use Webtolk\Max\Http\PsrHttpClient;
use Webtolk\Max\Module\BotModule;
use Webtolk\Max\Module\ChatModule;
use Webtolk\Max\Module\MessageModule;
use Webtolk\Max\Module\SubscriptionModule;
use Webtolk\Max\Module\UpdateModule;
use Webtolk\Max\Module\UploadModule;
use Webtolk\Max\Request\BotRequest;
use Webtolk\Max\Request\ChatRequest;
use Webtolk\Max\Request\MessageRequest;
use Webtolk\Max\Request\SubscriptionRequest;
use Webtolk\Max\Request\UpdateRequest;
use Webtolk\Max\Request\UploadRequest;

/**
 * Главный фасад SDK для работы с MAX API.
 * Нужен, чтобы один раз настроить transport и дальше получать типизированные модули библиотеки из единой точки входа.
 *
 * @since v.0.1.0
 */
final class Max
{
    private MaxConfig $config;
    private LoggerInterface $logger;
    private ?PsrHttpClient $transport = null;
    private ?BotModule $botModule = null;
    private ?MessageModule $messageModule = null;
    private ?ChatModule $chatModule = null;
    private ?UploadModule $uploadModule = null;
    private ?SubscriptionModule $subscriptionModule = null;
    private ?UpdateModule $updateModule = null;

    /**
     * Создаёт фасад SDK MAX с базовой конфигурацией и необязательным логгером.
     * Нужен, чтобы подготовить объект верхнего уровня, через который позже настраивается transport и открываются модули SDK.
     *
     * @param MaxConfig $config Конфигурация SDK с токеном доступа и базовыми transport-настройками.
     * @param ?LoggerInterface $logger PSR-3 логгер, который будет получать диагностические сообщения SDK.
     * @since v.0.1.0
     */
    public function __construct(
        MaxConfig $config,
        ?LoggerInterface $logger = null,
    ) {
        $this->config = $config;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Подключает PSR-18 клиент и PSR-17 фабрики к фасаду SDK.
     * Нужен, чтобы все последующие вызовы модулей могли реально отправлять HTTP-запросы в MAX API.
     *
     * @param ClientInterface $client PSR-18 HTTP-клиент, через который библиотека отправляет запросы к MAX API.
     * @param RequestFactoryInterface $requestFactory PSR-17 фабрика HTTP-запросов для построения исходящих вызовов.
     * @param StreamFactoryInterface $streamFactory PSR-17 фабрика потоков для JSON и бинарных тел запросов.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     */
    public function setTransport(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
    ): self {
        $this->transport = new PsrHttpClient(
            $client,
            $requestFactory,
            $streamFactory,
            $this->config,
            $this->logger,
        );
        $this->resetModules();

        return $this;
    }

    /**
     * Устанавливает PSR-3 логгер для фасада и активного transport-слоя.
     * Нужен, чтобы включить диагностические сообщения SDK без изменения остального прикладного кода.
     *
     * @param LoggerInterface $logger PSR-3 логгер, который будет получать диагностические сообщения SDK.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        $this->transport?->setLogger($logger);

        return $this;
    }

    /**
     * Возвращает значение `http client`.
     * Нужен, чтобы читать это значение из объекта `Max` без обращения к сырому payload MAX API.
     *
     * @return PsrHttpClient Настроенный transport-объект SDK, готовый к отправке запросов.
     * @since v.0.1.0
     */
    private function getHttpClient(): PsrHttpClient
    {
        if ($this->transport === null) {
            throw new InvalidArgumentException('Transport is not configured. Call setTransport() first.');
        }

        return $this->transport;
    }

    /**
     * Сбрасывает кэшированные экземпляры модулей SDK.
     * Нужен, чтобы после смены transport или логгера модули были пересозданы с актуальными зависимостями.
     *
     * @return void Метод ничего не возвращает; эффект достигается через изменение состояния объекта или побочный результат вызова.
     * @since v.0.1.0
     */
    private function resetModules(): void
    {
        $this->botModule = null;
        $this->messageModule = null;
        $this->chatModule = null;
        $this->uploadModule = null;
        $this->subscriptionModule = null;
        $this->updateModule = null;
    }

    /**
     * Возвращает модуль работы с профилем бота.
     * Нужен, чтобы вызывать bot-related методы SDK из фасада без ручного создания модульных объектов.
     *
     * @return BotModule Результат метода в виде объекта `BotModule`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     */
    public function bots(): BotModule
    {
        return $this->botModule ??= new BotModule(new BotRequest($this->getHttpClient()));
    }

    /**
     * Возвращает модуль работы с сообщениями.
     * Нужен, чтобы отправлять и читать сообщения через единый публичный вход в message API.
     *
     * @return MessageModule Результат метода в виде объекта `MessageModule`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     */
    public function messages(): MessageModule
    {
        return $this->messageModule ??= new MessageModule(new MessageRequest($this->getHttpClient()));
    }

    /**
     * Возвращает модуль чтения чатов и участников.
     * Нужен, чтобы получать чатовый контекст через фасад SDK.
     *
     * @return ChatModule Результат метода в виде объекта `ChatModule`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     */
    public function chats(): ChatModule
    {
        return $this->chatModule ??= new ChatModule(new ChatRequest($this->getHttpClient()));
    }

    /**
     * Возвращает модуль upload flow.
     * Нужен, чтобы инициировать загрузку файлов и получать токены вложений через фасад SDK.
     *
     * @return UploadModule Результат метода в виде объекта `UploadModule`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     */
    public function uploads(): UploadModule
    {
        return $this->uploadModule ??= new UploadModule(new UploadRequest($this->getHttpClient()));
    }

    /**
     * Возвращает модуль webhook-подписок.
     * Нужен, чтобы создавать, читать и удалять подписки на события MAX API.
     *
     * @return SubscriptionModule Результат метода в виде объекта `SubscriptionModule`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     */
    public function subscriptions(): SubscriptionModule
    {
        return $this->subscriptionModule ??= new SubscriptionModule(new SubscriptionRequest($this->getHttpClient()));
    }

    /**
     * Возвращает модуль long polling обновлений.
     * Нужен, чтобы получать события MAX API через фасад SDK.
     *
     * @return UpdateModule Результат метода в виде объекта `UpdateModule`, подготовленного для дальнейшего использования в SDK или прикладном коде.
     * @since v.0.1.0
     */
    public function updates(): UpdateModule
    {
        return $this->updateModule ??= new UpdateModule(new UpdateRequest($this->getHttpClient()));
    }
}
