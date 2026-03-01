<?php

declare(strict_types=1);

namespace FoodOS\Domain\Menu;

use FoodOS\Core\AbstractModule;

final class MenuModule extends AbstractModule
{
    public function name(): string
    {
        return 'Menu';
    }

    protected function scope(): array
    {
        return ['Menu, warianty produktów i ceny dynamiczne', 'Walidacja danych', 'Obsługa błędów', 'Wydajność i skalowalność'];
    }

    protected function dependencyContracts(): array
    {
        return ['InventoryGateway, PricingEngine'];
    }
}
