<?php

declare(strict_types=1);

namespace FoodOS\Domain\Franchise;

use FoodOS\Core\AbstractModule;

final class FranchiseModule extends AbstractModule
{
    public function name(): string
    {
        return 'Franchise';
    }

    protected function scope(): array
    {
        return ['Zarządzanie franczyzą', 'Walidacja danych', 'Obsługa błędów', 'Wydajność i skalowalność'];
    }

    protected function dependencyContracts(): array
    {
        return ['ContractPolicy, RoyaltyCalculator, BranchSync'];
    }
}
