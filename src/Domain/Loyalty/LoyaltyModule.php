<?php

declare(strict_types=1);

namespace FoodOS\Domain\Loyalty;

use FoodOS\Core\AbstractModule;

final class LoyaltyModule extends AbstractModule
{
    public function name(): string
    {
        return 'Loyalty';
    }

    protected function scope(): array
    {
        return ['Program lojalnościowy i wallet', 'Walidacja danych', 'Obsługa błędów', 'Wydajność i skalowalność'];
    }

    protected function dependencyContracts(): array
    {
        return ['PointsLedger, RewardsEngine, WalletProvider'];
    }
}
