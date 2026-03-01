<?php

declare(strict_types=1);

namespace FoodOS\Domain\Order;

use FoodOS\Core\AbstractModule;

final class OrderModule extends AbstractModule
{
    public function name(): string
    {
        return 'Order';
    }

    protected function scope(): array
    {
        return ['Obsługa zamówień i statusów', 'Walidacja danych', 'Obsługa błędów', 'Wydajność i skalowalność'];
    }

    protected function dependencyContracts(): array
    {
        return ['DeliveryGateway, KitchenQueue, EventBus'];
    }
}
