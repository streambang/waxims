# FoodOS Enterprise Blueprint

## Cel

Gotowy fundament implementacyjny platformy FoodOS dla modelu B2B/B2C/B2B2C w PHP 8.2, z naciskiem na:

- architekturę modułową zgodną z SOLID,
- bezpieczeństwo (JWT, hashowanie haseł, audyt),
- skalowalność (multi-tenant, indeksowanie, podział domen),
- gotowość do mikroserwisów.

## Warstwy

1. **Presentation** – API REST / kontrolery.
2. **Application** – orchestration use-case'ów.
3. **Domain** – logika biznesowa i kontrakty.
4. **Infrastructure** – repozytoria, integracje zewnętrzne.
5. **Data** – MySQL + indeksy + constraints.

## Moduły domenowe

W `src/Domain/*` przygotowano moduły:

- Auth,
- Restaurant,
- Menu,
- Cart,
- Order,
- Payment,
- Loyalty,
- Analytics,
- Notification,
- Integration,
- Tenant,
- Franchise.

Każdy moduł definiuje:

- odpowiedzialności,
- podstawowe integracje,
- wspólny kontrakt pozwalający testować moduły niezależnie.

## Baza danych (MySQL)

Plik `database/schema.sql` dostarcza strukturę pod:

- tenancy (`tenants`),
- tożsamość i role (`users`),
- katalog restauracji i lokali (`restaurants`, `restaurant_branches`),
- menu i warianty (`menu_items`, `menu_item_variants`),
- zamówienia i pozycje (`orders`, `order_items`),
- płatności (`payments`),
- lojalność (`loyalty_wallets`),
- audyt (`audit_logs`).

## Decyzje architektoniczne

- Wymuszenie `declare(strict_types=1)` dla kodu PHP.
- Podejście API-first (moduły mapowalne do endpointów).
- Klucze obce i indeksy pod zapytania operacyjne.
- Ready-to-split: moduły można wydzielić do mikroserwisów.

## Następne kroki

1. Dodać kontrolery MVC i DTO dla każdego modułu.
2. Dodać implementacje repozytoriów (MySQL + cache).
3. Dodać JWT auth middleware i rate limiting.
4. Dodać testy jednostkowe i integracyjne do CI.
