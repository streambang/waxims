<?php

declare(strict_types=1);

namespace FoodOS\Domain\Cart;

use FoodOS\Core\AbstractModule;

final class CartModule extends AbstractModule
{
    public function name(): string
    {
        return 'Cart';
    }

    protected function scope(): array
    {
        return ['Koszyk i kalkulacja cen', 'Walidacja danych', 'Obsługa błędów', 'Wydajność i skalowalność'];
    }

    protected function dependencyContracts(): array
    {
        return ['PromotionEngine, TaxPolicy, AvailabilityChecker'];
    }
}
