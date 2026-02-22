<?php

declare(strict_types=1);

namespace FoodOS\Domain\Auth;

use FoodOS\Core\AbstractModule;

final class AuthModule extends AbstractModule
{
    public function name(): string
    {
        return 'Auth';
    }

    protected function scope(): array
    {
        return ['Autoryzacja i tożsamość', 'Walidacja danych', 'Obsługa błędów', 'Wydajność i skalowalność'];
    }

    protected function dependencyContracts(): array
    {
        return ['JWT, PasswordHasher, AuditLog'];
    }
}
