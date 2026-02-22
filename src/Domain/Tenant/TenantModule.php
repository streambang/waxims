<?php

declare(strict_types=1);

namespace FoodOS\Domain\Tenant;

use FoodOS\Core\AbstractModule;

final class TenantModule extends AbstractModule
{
    public function name(): string
    {
        return 'Tenant';
    }

    protected function scope(): array
    {
        return ['Multi-tenant i konfiguracja tenantów', 'Walidacja danych', 'Obsługa błędów', 'Wydajność i skalowalność'];
    }

    protected function dependencyContracts(): array
    {
        return ['TenantResolver, FeatureFlags, SecretsVault'];
    }
}
