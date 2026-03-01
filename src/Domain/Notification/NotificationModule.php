<?php

declare(strict_types=1);

namespace FoodOS\Domain\Notification;

use FoodOS\Core\AbstractModule;

final class NotificationModule extends AbstractModule
{
    public function name(): string
    {
        return 'Notification';
    }

    protected function scope(): array
    {
        return ['Powiadomienia email/sms/push', 'Walidacja danych', 'Obsługa błędów', 'Wydajność i skalowalność'];
    }

    protected function dependencyContracts(): array
    {
        return ['EmailProvider, SmsProvider, PushProvider'];
    }
}
