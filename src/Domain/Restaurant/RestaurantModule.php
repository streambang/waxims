<?php

declare(strict_types=1);

namespace FoodOS\Domain\Restaurant;

use FoodOS\Core\AbstractModule;

final class RestaurantModule extends AbstractModule
{
    public function name(): string
    {
        return 'Restaurant';
    }

    protected function scope(): array
    {
        return ['Katalog restauracji i lokali', 'Walidacja danych', 'Obsługa błędów', 'Wydajność i skalowalność'];
    }

    protected function dependencyContracts(): array
    {
        return ['GeoIndex, SearchEngine, POSConnector'];
    }
}
