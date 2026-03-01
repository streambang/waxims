# FoodOS (waxims)

Szkielet platformy Enterprise dla rynku gastronomicznego (B2B/B2C/B2B2C) w PHP 8.2.

## Co jest gotowe

- modularny szkielet domenowy (`src/Domain/*`),
- kontrakt modułów i agregacja architektury (`src/Core/*`),
- bootstrap rejestrujący moduły (`src/Application/bootstrap.php`),
- projekt relacyjnej bazy danych MySQL pod multi-tenant (`database/schema.sql`),
- dokumentacja architektury (`docs/foodos-enterprise-blueprint.md`).

## Uruchomienie lokalne

```bash
composer dump-autoload
php -r '$architecture = require "src/Application/bootstrap.php"; var_export($architecture->describe());'
```

## Założenia

- `strict_types=1` wszędzie,
- gotowość pod mikroserwisy,
- separacja odpowiedzialności zgodnie z SOLID,
- bezpieczeństwo i audyt jako element architektury.
