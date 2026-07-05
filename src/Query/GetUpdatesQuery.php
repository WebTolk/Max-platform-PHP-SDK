<?php

declare(strict_types=1);

namespace Webtolk\Max\Query;

use Webtolk\Max\Exception\ValidationException;
use Webtolk\Max\Support\UpdateTypeNormalizer;

/**
 * Query-объект SDK `GetUpdatesQuery` для параметров запроса.
 * Нужен, чтобы собирать и валидировать query-параметры MAX API отдельно от модульного и transport-слоя.
 *
 * @since v.0.1.0
 * @link https://dev.max.ru/docs-api/methods/GET/updates
 */
final class GetUpdatesQuery
{
    private ?int $marker = null;
    private ?int $limit = null;
    private ?int $timeout = null;
    /** @var list<string> */
    private array $types = [];

    /**
     * Создаёт query-объект со значениями по умолчанию.
     * Нужен, чтобы быстро получить рабочую конфигурацию запроса без ручной инициализации каждого параметра.
     *
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     */
    public static function default(): self
    {
        return new self();
    }

    /**
     * Создаёт query, продолжающий long polling с указанного маркера.
     * Нужен, чтобы интеграция могла продолжать чтение update с сохранённой позиции.
     *
     * @param int $marker Маркер пагинации или позиция long polling, с которой нужно продолжить чтение.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     */
    public static function fromMarker(int $marker): self
    {
        $self = new self();
        $self->marker = $marker;

        return $self;
    }

    /**
     * Устанавливает лимит числа update в одном ответе.
     * Нужен, чтобы контролировать размер порции событий при long polling.
     *
     * @param int $limit Лимит числа update, которое MAX API должен вернуть за один вызов.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     */
    public function withLimit(int $limit): self
    {
        if ($limit < 1 || $limit > 1000) {
            throw new ValidationException('limit must be in range 1..1000.');
        }

        $this->limit = $limit;

        return $this;
    }

    /**
     * Устанавливает timeout long polling запроса.
     * Нужен, чтобы согласовать длительность ожидания событий с политикой интеграции.
     *
     * @param int $timeout Таймаут long polling в секундах или в формате, который ожидает MAX API.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     */
    public function withTimeout(int $timeout): self
    {
        if ($timeout < 0 || $timeout > 90) {
            throw new ValidationException('timeout must be in range 0..90.');
        }

        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Ограничивает long polling выбранными типами update.
     * Нужен, чтобы MAX API возвращал только нужные интеграции события.
     *
     * @param string ...$types Список имён типов обновлений, которые нужно нормализовать или отправить в API.
     * @return self Текущий экземпляр объекта для продолжения fluent-цепочки вызовов.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     */
    public function withTypes(string ...$types): self
    {
        $this->types = UpdateTypeNormalizer::normalize(array_values($types));

        return $this;
    }

    /**
     * Сериализует объект в query-параметры MAX API.
     * Нужен, чтобы request-слой мог построить корректный URL и строку запроса из типизированного объекта.
     *
     * @return array<string, int|list<string>> Массив query-параметров в формате, который ожидает MAX API.
     * @since v.0.1.0
     * @link https://dev.max.ru/docs-api/methods/GET/updates
     */
    public function toQueryParams(): array
    {
        $params = [];

        if ($this->marker !== null) {
            $params['marker'] = $this->marker;
        }

        if ($this->limit !== null) {
            $params['limit'] = $this->limit;
        }

        if ($this->timeout !== null) {
            $params['timeout'] = $this->timeout;
        }

        if ($this->types !== []) {
            $params['types'] = $this->types;
        }

        return $params;
    }
}
