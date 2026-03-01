<?php

declare(strict_types=1);

namespace FoodOS\Domain\Analytics;

use FoodOS\Core\AbstractModule;

final class AnalyticsModule extends AbstractModule
{
    public function name(): string
    {
        return 'Analytics';
    }

    protected function scope(): array
    {
        return ['Analityka i BI', 'Walidacja danych', 'Obsługa błędów', 'Wydajność i skalowalność'];
    }

    protected function dependencyContracts(): array
    {
        return ['DataWarehouse, KPIEngine, ForecastModel'];
    }
}
