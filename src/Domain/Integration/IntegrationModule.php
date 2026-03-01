<?php

declare(strict_types=1);

namespace FoodOS\Domain\Integration;

use FoodOS\Core\AbstractModule;

final class IntegrationModule extends AbstractModule
{
    public function name(): string
    {
        return 'Integration';
    }

    protected function scope(): array
    {
        return ['API i integracje zewnętrzne', 'Walidacja danych', 'Obsługa błędów', 'Wydajność i skalowalność'];
    }

    protected function dependencyContracts(): array
    {
        return ['WebhookDispatcher, OAuthClient, RateLimiter'];
    }
}
