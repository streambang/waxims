<?php

declare(strict_types=1);

use FoodOS\Core\SystemArchitecture;
use FoodOS\Domain\Analytics\AnalyticsModule;
use FoodOS\Domain\Auth\AuthModule;
use FoodOS\Domain\Cart\CartModule;
use FoodOS\Domain\Franchise\FranchiseModule;
use FoodOS\Domain\Integration\IntegrationModule;
use FoodOS\Domain\Loyalty\LoyaltyModule;
use FoodOS\Domain\Menu\MenuModule;
use FoodOS\Domain\Notification\NotificationModule;
use FoodOS\Domain\Order\OrderModule;
use FoodOS\Domain\Payment\PaymentModule;
use FoodOS\Domain\Restaurant\RestaurantModule;
use FoodOS\Domain\Tenant\TenantModule;

return new SystemArchitecture([
    new AuthModule(),
    new RestaurantModule(),
    new MenuModule(),
    new CartModule(),
    new OrderModule(),
    new PaymentModule(),
    new LoyaltyModule(),
    new AnalyticsModule(),
    new NotificationModule(),
    new IntegrationModule(),
    new TenantModule(),
    new FranchiseModule(),
]);
