<?php

declare(strict_types=1);

namespace FoodOS\Domain\Payment;

use FoodOS\Core\AbstractModule;

final class PaymentModule extends AbstractModule
{
    public function name(): string
    {
        return 'Payment';
    }

    protected function scope(): array
    {
        return ['Płatności i rozliczenia', 'Walidacja danych', 'Obsługa błędów', 'Wydajność i skalowalność'];
    }

    protected function dependencyContracts(): array
    {
        return ['PaymentGateway, FraudDetection, InvoiceService'];
    }
}
